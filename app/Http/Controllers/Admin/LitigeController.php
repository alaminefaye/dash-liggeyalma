<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Litige;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LitigeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Litige::with(['commande', 'client.user', 'prestataire.user', 'traitePar']);

        // Recherche
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('id', $search)
                  ->orWhereHas('client.user', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('prestataire.user', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
        }

        // Filtre par statut
        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }

        // Filtre par type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filtre par date
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $litiges = $query->latest()->paginate(15);

        // Statistiques
        $stats = [
            'total' => Litige::count(),
            'en_attente' => Litige::where('statut', 'en_attente')->count(),
            'en_cours' => Litige::where('statut', 'en_cours')->count(),
            'resolus' => Litige::where('statut', 'resolu')->count(),
        ];

        return view('admin.litiges.index', compact('litiges', 'stats'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Litige $litige)
    {
        $litige->load(['commande', 'client.user', 'prestataire.user', 'traitePar']);
        return view('admin.litiges.show', compact('litige'));
    }

    /**
     * Traiter un litige
     */
    public function process(Request $request, Litige $litige)
    {
        $validated = $request->validate([
            'resolution' => 'required|string|max:1000',
            'decision' => 'required|in:remboursement,remediation,rejet',
            'montant_remboursement' => 'nullable|numeric|min:0',
        ]);

        DB::transaction(function() use ($litige, $validated) {
            $litige->update([
                'statut' => 'en_cours',
                'resolution' => $validated['resolution'],
                'decision' => $validated['decision'],
                'montant_remboursement' => $validated['montant_remboursement'] ?? null,
                'traite_par' => auth()->id(),
                'traite_le' => now(),
            ]);

            // Si remboursement, créer une transaction
            if ($validated['decision'] === 'remboursement' && $validated['montant_remboursement']) {
                \App\Models\Transaction::create([
                    'commande_id' => $litige->commande_id,
                    'client_id' => $litige->client_id,
                    'prestataire_id' => $litige->prestataire_id,
                    'type' => 'refund',
                    'montant' => $validated['montant_remboursement'],
                    'statut' => 'validee',
                    'description' => 'Remboursement suite à litige #' . $litige->id,
                ]);
            }
        });

        return redirect()->back()
            ->with('success', 'Litige traité avec succès.');
    }

    /**
     * Clôturer un litige
     */
    public function close(Litige $litige)
    {
        $litige->update(['statut' => 'clos']);

        return redirect()->back()
            ->with('success', 'Litige clôturé avec succès.');
    }

    /**
     * Résoudre un litige
     */
    public function resolve(Request $request, Litige $litige)
    {
        $validated = $request->validate([
            'resolution' => 'required|string|max:1000',
        ]);

        $litige->update([
            'statut' => 'resolu',
            'resolution' => $validated['resolution'],
            'traite_par' => auth()->id(),
            'traite_le' => now(),
        ]);

        return redirect()->back()
            ->with('success', 'Litige résolu avec succès.');
    }

    /**
     * Rembourser directement un litige
     */
    public function refund(Request $request, Litige $litige)
    {
        $validated = $request->validate([
            'montant_remboursement' => 'required|numeric|min:0',
            'resolution' => 'nullable|string|max:1000',
        ]);

        DB::transaction(function() use ($litige, $validated) {
            $litige->update([
                'statut' => 'resolu',
                'decision' => 'remboursement',
                'montant_remboursement' => $validated['montant_remboursement'],
                'resolution' => $validated['resolution'] ?? 'Remboursement effectué',
                'traite_par' => auth()->id(),
                'traite_le' => now(),
            ]);

            // Créer la transaction de remboursement
            \App\Models\Transaction::create([
                'commande_id' => $litige->commande_id,
                'client_id' => $litige->client_id,
                'prestataire_id' => $litige->prestataire_id,
                'type' => 'refund',
                'montant' => $validated['montant_remboursement'],
                'statut' => 'validee',
                'description' => 'Remboursement direct suite à litige #' . $litige->id,
            ]);
        });

        return redirect()->back()
            ->with('success', 'Remboursement effectué avec succès.');
    }
}
