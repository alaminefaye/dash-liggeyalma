<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Retrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RetraitController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Retrait::with(['prestataire.user', 'validePar']);

        // Recherche
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('id', $search)
                  ->orWhereHas('prestataire.user', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                  });
        }

        // Filtre par statut
        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }

        // Filtre par méthode
        if ($request->filled('methode')) {
            $query->where('methode', $request->methode);
        }

        // Filtre par date
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $retraits = $query->latest()->paginate(15);

        // Statistiques
        $stats = [
            'en_attente' => Retrait::where('statut', 'en_attente')->count(),
            'total_montant_attente' => Retrait::where('statut', 'en_attente')->sum('montant'),
            'valides' => Retrait::where('statut', 'valide')->sum('montant'),
        ];

        return view('admin.retraits.index', compact('retraits', 'stats'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Retrait $retrait)
    {
        $retrait->load(['prestataire.user', 'validePar']);
        return view('admin.retraits.show', compact('retrait'));
    }

    /**
     * Valider un retrait
     */
    public function validate(Retrait $retrait)
    {
        if ($retrait->statut !== 'en_attente') {
            return redirect()->back()
                ->with('error', 'Ce retrait ne peut plus être validé.');
        }

        // Vérifier que le prestataire a assez de solde
        if ($retrait->prestataire->solde < $retrait->montant) {
            return redirect()->back()
                ->with('error', 'Le solde du prestataire est insuffisant.');
        }

        DB::transaction(function() use ($retrait) {
            // Débiter le solde du prestataire
            $retrait->prestataire->decrement('solde', $retrait->montant);

            // Valider le retrait
            $retrait->update([
                'statut' => 'valide',
                'date_validation' => now(),
                'valide_par' => auth()->id(),
            ]);

            // Créer une transaction de type retrait
            \App\Models\Transaction::create([
                'prestataire_id' => $retrait->prestataire_id,
                'type' => 'retrait',
                'montant' => $retrait->montant_net,
                'commission' => $retrait->frais_retrait,
                'methode_paiement' => $retrait->methode,
                'statut' => 'validee',
                'reference_externe' => 'RETRAIT-' . $retrait->id,
            ]);
        });

        return redirect()->back()
            ->with('success', 'Retrait validé avec succès. Le solde du prestataire a été débité.');
    }

    /**
     * Refuser un retrait
     */
    public function reject(Request $request, Retrait $retrait)
    {
        $validated = $request->validate([
            'motif_refus' => 'required|string|max:500',
        ]);

        $retrait->update([
            'statut' => 'refuse',
            'motif_refus' => $validated['motif_refus'],
        ]);

        return redirect()->back()
            ->with('success', 'Retrait refusé.');
    }
}
