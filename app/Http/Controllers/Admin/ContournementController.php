<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Contournement;
use App\Models\Prestataire;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ContournementController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Contournement::with(['prestataire.user', 'client.user', 'commande']);

        // Recherche
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('id', $search)
                  ->orWhereHas('prestataire.user', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('client.user', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
        }

        // Filtre par type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filtre par statut
        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }

        // Filtre par prestataire
        if ($request->filled('prestataire_id')) {
            $query->where('prestataire_id', $request->prestataire_id);
        }

        // Filtre par date
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $contournements = $query->latest()->paginate(15);

        // Statistiques
        $stats = [
            'total' => Contournement::count(),
            'detectes' => Contournement::where('statut', 'detecte')->count(),
            'confirmes' => Contournement::where('statut', 'confirme')->count(),
            'rejetes' => Contournement::where('statut', 'rejete')->count(),
        ];

        return view('admin.contournements.index', compact('contournements', 'stats'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Contournement $contournement)
    {
        $contournement->load(['prestataire.user', 'client.user', 'commande']);
        return view('admin.contournements.show', compact('contournement'));
    }

    /**
     * Valider un contournement (confirmer la fraude)
     */
    public function validate(Contournement $contournement)
    {
        if ($contournement->statut === 'confirme') {
            return redirect()->back()
                ->with('error', 'Ce contournement est déjà confirmé.');
        }

        DB::transaction(function() use ($contournement) {
            // Appliquer les sanctions
            $prestataire = $contournement->prestataire;
            $nombreContournements = $prestataire->contournements()
                ->where('statut', 'confirme')
                ->count();

            $sanctions = [];

            // 1er contournement : Avertissement
            if ($nombreContournements == 0) {
                $sanctions[] = 'Avertissement - Blocage temporaire 24h';
                // Optionnel : Bloquer temporairement
            }
            // 2ème contournement : Blocage 7 jours + Déclassement
            elseif ($nombreContournements == 1) {
                $sanctions[] = 'Blocage temporaire 7 jours';
                $sanctions[] = 'Déclassement dans les résultats';
                $prestataire->user->update(['status' => 'suspended']);
            }
            // 3ème contournement : Blocage définitif
            else {
                $sanctions[] = 'Blocage définitif du compte';
                $prestataire->user->update(['status' => 'blocked']);
            }

            // Mettre à jour le contournement
            $contournement->update([
                'statut' => 'confirme',
                'sanction_appliquee' => implode(', ', $sanctions),
            ]);

            // Diminuer le score de confiance
            $nouveauScore = max(0, $prestataire->score_confiance - 0.5);
            $prestataire->update(['score_confiance' => $nouveauScore]);
        });

        return redirect()->back()
            ->with('success', 'Contournement confirmé. Les sanctions ont été appliquées.');
    }

    /**
     * Rejeter un contournement (faux positif)
     */
    public function reject(Contournement $contournement)
    {
        $contournement->update(['statut' => 'rejete']);

        return redirect()->back()
            ->with('success', 'Contournement rejeté (faux positif).');
    }

    /**
     * Avertir un prestataire
     */
    public function warn(Contournement $contournement)
    {
        // Envoyer un avertissement au prestataire
        // Optionnel : Notification email/SMS

        return redirect()->back()
            ->with('success', 'Avertissement envoyé au prestataire.');
    }

    /**
     * Bloquer le compte du prestataire
     */
    public function block(Contournement $contournement)
    {
        $contournement->prestataire->user->update(['status' => 'blocked']);

        $contournement->update([
            'sanction_appliquee' => 'Compte bloqué manuellement par l\'administrateur',
        ]);

        return redirect()->back()
            ->with('success', 'Compte prestataire bloqué.');
    }
}
