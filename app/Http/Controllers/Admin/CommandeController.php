<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Commande;
use App\Exports\CommandesExport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class CommandeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Commande::with(['client.user', 'prestataire.user', 'categorieService']);

        // Recherche
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('id', $search)
                  ->orWhereHas('client.user', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                  })
                  ->orWhereHas('prestataire.user', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
        }

        // Filtre par statut
        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }

        // Filtre par client
        if ($request->filled('client_id')) {
            $query->where('client_id', $request->client_id);
        }

        // Filtre par prestataire
        if ($request->filled('prestataire_id')) {
            $query->where('prestataire_id', $request->prestataire_id);
        }

        // Filtre par catégorie
        if ($request->filled('categorie_id')) {
            $query->where('categorie_service_id', $request->categorie_id);
        }

        // Filtre par date
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $commandes = $query->latest()->paginate(15);

        // Pour les filtres
        $clients = \App\Models\Client::with('user')->get();
        $prestataires = \App\Models\Prestataire::with('user')->get();
        $categories = \App\Models\CategorieService::where('active', true)->get();

        return view('admin.commandes.index', compact('commandes', 'clients', 'prestataires', 'categories'));
    }

    /**
     * Export commandes to Excel
     */
    public function export()
    {
        return Excel::download(new CommandesExport, 'commandes_' . date('Y-m-d_His') . '.xlsx');
    }

    /**
     * Display the specified resource.
     */
    public function show(Commande $commande)
    {
        $commande->load([
            'client.user',
            'prestataire.user',
            'categorieService',
            'sousCategorieService',
            'transaction',
            'avis'
        ]);

        return view('admin.commandes.show', compact('commande'));
    }

    /**
     * Update the status of a commande
     */
    public function updateStatus(Request $request, Commande $commande)
    {
        $validated = $request->validate([
            'statut' => 'required|in:en_attente,acceptee,en_route,arrivee,en_cours,terminee,annulee',
        ]);

        // Enregistrer l'historique
        $historique = $commande->historique_statuts ?? [];
        $historique[] = [
            'ancien_statut' => $commande->statut,
            'nouveau_statut' => $validated['statut'],
            'date' => now()->toDateTimeString(),
            'par' => auth()->user()->name,
        ];

        $commande->update([
            'statut' => $validated['statut'],
            'historique_statuts' => $historique,
        ]);

        return redirect()->back()
            ->with('success', 'Statut de la commande mis à jour avec succès.');
    }

    /**
     * Cancel a commande
     */
    public function cancel(Commande $commande)
    {
        if ($commande->statut === 'terminee') {
            return redirect()->back()
                ->with('error', 'Impossible d\'annuler une commande terminée.');
        }

        // Enregistrer l'historique
        $historique = $commande->historique_statuts ?? [];
        $historique[] = [
            'ancien_statut' => $commande->statut,
            'nouveau_statut' => 'annulee',
            'date' => now()->toDateTimeString(),
            'par' => auth()->user()->name,
            'raison' => 'Annulée par l\'administrateur',
        ];

        $commande->update([
            'statut' => 'annulee',
            'historique_statuts' => $historique,
        ]);

        return redirect()->back()
            ->with('success', 'Commande annulée avec succès.');
    }

    /**
     * Generate invoice for a commande
     */
    public function generateInvoice(Commande $commande)
    {
        $commande->load(['client.user', 'prestataire.user', 'categorieService']);
        
        // Ici vous pouvez générer un PDF de facture
        // Pour l'instant, on retourne juste une vue
        return view('admin.commandes.invoice', compact('commande'));
    }
}
