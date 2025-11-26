<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Prestataire;
use App\Exports\PrestatairesExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class PrestataireController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Prestataire::with('user');

        // Recherche
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            })->orWhere('metier', 'like', "%{$search}%");
        }

        // Filtre par statut
        if ($request->filled('status')) {
            $query->whereHas('user', function($q) use ($request) {
                $q->where('status', $request->status);
            });
        }

        // Filtre par statut d'inscription
        if ($request->filled('statut_inscription')) {
            $query->where('statut_inscription', $request->statut_inscription);
        }

        // Filtre solde négatif
        if ($request->filled('solde_negatif') && $request->solde_negatif == '1') {
            $query->where('solde', '<', 0);
        }

        $prestataires = $query->latest()->paginate(15);

        return view('admin.prestataires.index', compact('prestataires'));
    }

    /**
     * Export prestataires to Excel
     */
    public function export()
    {
        return Excel::download(new PrestatairesExport, 'prestataires_' . date('Y-m-d_His') . '.xlsx');
    }

    /**
     * Liste des prestataires en attente de validation
     */
    public function pending()
    {
        $prestataires = Prestataire::with('user')
            ->where('statut_inscription', 'en_attente')
            ->latest()
            ->paginate(15);

        return view('admin.prestataires.pending', compact('prestataires'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.prestataires.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:20',
            'password' => 'required|string|min:8',
            'metier' => 'required|string|max:255',
            'description' => 'nullable|string',
            'tarif_horaire' => 'nullable|numeric|min:0',
        ]);

        DB::transaction(function() use ($validated) {
            $user = \App\Models\User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
                'password' => bcrypt($validated['password']),
                'role' => 'prestataire',
                'status' => 'active',
            ]);

            Prestataire::create([
                'user_id' => $user->id,
                'metier' => $validated['metier'],
                'description' => $validated['description'] ?? null,
                'tarif_horaire' => $validated['tarif_horaire'] ?? null,
                'statut_inscription' => 'valide', // Directement validé si créé par admin
            ]);
        });

        return redirect()->route('admin.prestataires.index')
            ->with('success', 'Prestataire créé avec succès.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Prestataire $prestataire)
    {
        $prestataire->load([
            'user',
            'commandes.client.user',
            'commandes.categorieService',
            'avis.client.user',
            'retraits',
            'contournements'
        ]);
        
        $stats = [
            'interventions_total' => $prestataire->commandes()->count(),
            'interventions_terminees' => $prestataire->commandes()->where('statut', 'terminee')->count(),
            'avis_recus' => $prestataire->avis()->count(),
            'note_moyenne' => $prestataire->avis()->avg('note') ?? 0,
            'revenus_total' => $prestataire->transactions()->where('type', 'paiement')->sum('montant'),
            'commission_payee' => $prestataire->transactions()->where('type', 'commission')->sum('montant'),
        ];

        return view('admin.prestataires.show', compact('prestataire', 'stats'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Prestataire $prestataire)
    {
        $prestataire->load('user');
        return view('admin.prestataires.edit', compact('prestataire'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Prestataire $prestataire)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $prestataire->user_id,
            'phone' => 'nullable|string|max:20',
            'password' => 'nullable|string|min:8',
            'metier' => 'required|string|max:255',
            'description' => 'nullable|string',
            'tarif_horaire' => 'nullable|numeric|min:0',
            'status' => 'required|in:active,suspended,blocked',
        ]);

        DB::transaction(function() use ($validated, $prestataire) {
            $userData = [
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
                'status' => $validated['status'],
            ];

            if (!empty($validated['password'])) {
                $userData['password'] = bcrypt($validated['password']);
            }

            $prestataire->user->update($userData);

            $prestataire->update([
                'metier' => $validated['metier'],
                'description' => $validated['description'] ?? null,
                'tarif_horaire' => $validated['tarif_horaire'] ?? null,
            ]);
        });

        return redirect()->route('admin.prestataires.show', $prestataire)
            ->with('success', 'Prestataire mis à jour avec succès.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Prestataire $prestataire)
    {
        DB::transaction(function() use ($prestataire) {
            $prestataire->user->delete();
            $prestataire->delete();
        });

        return redirect()->route('admin.prestataires.index')
            ->with('success', 'Prestataire supprimé avec succès.');
    }

    /**
     * Valider un compte prestataire
     */
    public function validate(Prestataire $prestataire)
    {
        $prestataire->update([
            'statut_inscription' => 'valide'
        ]);

        // Optionnel : Envoyer une notification au prestataire

        return redirect()->back()
            ->with('success', 'Compte prestataire validé avec succès.');
    }

    /**
     * Refuser un compte prestataire
     */
    public function reject(Request $request, Prestataire $prestataire)
    {
        $validated = $request->validate([
            'motif' => 'required|string|max:500',
        ]);

        $prestataire->update([
            'statut_inscription' => 'refuse'
        ]);

        // Optionnel : Envoyer une notification avec le motif

        return redirect()->back()
            ->with('success', 'Compte prestataire refusé.');
    }

    /**
     * Suspendre un prestataire
     */
    public function suspend(Prestataire $prestataire)
    {
        $prestataire->user->update(['status' => 'suspended']);

        return redirect()->back()
            ->with('success', 'Prestataire suspendu avec succès.');
    }

    /**
     * Réactiver un prestataire
     */
    public function activate(Prestataire $prestataire)
    {
        $prestataire->user->update(['status' => 'active']);

        return redirect()->back()
            ->with('success', 'Prestataire réactivé avec succès.');
    }

    /**
     * Bloquer un prestataire
     */
    public function block(Prestataire $prestataire)
    {
        $prestataire->user->update(['status' => 'blocked']);

        return redirect()->back()
            ->with('success', 'Prestataire bloqué avec succès.');
    }

    /**
     * Débloquer un prestataire
     */
    public function unblock(Prestataire $prestataire)
    {
        $prestataire->user->update(['status' => 'active']);

        return redirect()->back()
            ->with('success', 'Prestataire débloqué avec succès.');
    }

    /**
     * Forcer le paiement de commission (si solde négatif)
     */
    public function forceCommissionPayment(Prestataire $prestataire)
    {
        // Cette fonctionnalité sera implémentée plus tard avec le système de paiement
        return redirect()->back()
            ->with('info', 'Fonctionnalité en cours de développement.');
    }
}
