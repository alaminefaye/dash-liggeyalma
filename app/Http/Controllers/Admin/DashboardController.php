<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Prestataire;
use App\Models\Commande;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Afficher le tableau de bord admin
     */
    public function index()
    {
        // Statistiques globales
        $stats = [
            'clients_total' => Client::count(),
            'clients_actifs' => Client::whereHas('user', function($query) {
                $query->where('status', 'active');
            })->count(),
            'clients_nouveaux_mois' => Client::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
            
            'prestataires_total' => Prestataire::count(),
            'prestataires_actifs' => Prestataire::whereHas('user', function($query) {
                $query->where('status', 'active');
            })->count(),
            'prestataires_en_attente' => Prestataire::where('statut_inscription', 'en_attente')->count(),
            'prestataires_valides' => Prestataire::where('statut_inscription', 'valide')->count(),
            
            'commandes_total' => Commande::count(),
            'commandes_en_cours' => Commande::whereIn('statut', ['en_attente', 'acceptee', 'en_route', 'arrivee', 'en_cours'])->count(),
            'commandes_terminees' => Commande::where('statut', 'terminee')->count(),
            'commandes_annulees' => Commande::where('statut', 'annulee')->count(),
            'commandes_aujourdhui' => Commande::whereDate('created_at', today())->count(),
            
            'revenus_total' => Transaction::where('type', 'paiement')
                ->where('statut', 'validee')
                ->sum('montant'),
            'commission_totale' => Transaction::where('type', 'commission')
                ->where('statut', 'validee')
                ->sum('montant'),
            'revenus_mois' => Transaction::where('type', 'paiement')
                ->where('statut', 'validee')
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->sum('montant'),
        ];

        // Évolution des utilisateurs (7 derniers jours)
        $evolution_users = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $evolution_users[] = [
                'date' => $date->format('Y-m-d'),
                'label' => $date->format('d/m'),
                'clients' => Client::whereDate('created_at', $date)->count(),
                'prestataires' => Prestataire::whereDate('created_at', $date)->count(),
            ];
        }

        // Évolution des commandes (7 derniers jours)
        $evolution_commandes = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $evolution_commandes[] = [
                'date' => $date->format('Y-m-d'),
                'label' => $date->format('d/m'),
                'total' => Commande::whereDate('created_at', $date)->count(),
            ];
        }

        // Évolution des revenus (7 derniers jours)
        $evolution_revenus = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $evolution_revenus[] = [
                'date' => $date->format('Y-m-d'),
                'label' => $date->format('d/m'),
                'montant' => Transaction::where('type', 'commission')
                    ->where('statut', 'validee')
                    ->whereDate('created_at', $date)
                    ->sum('montant'),
            ];
        }

        // Répartition des commandes par statut
        $commandes_par_statut = Commande::select('statut', DB::raw('count(*) as total'))
            ->groupBy('statut')
            ->get()
            ->pluck('total', 'statut')
            ->toArray();

        // Répartition par catégorie de service
        $commandes_par_categorie = DB::table('commandes')
            ->join('categorie_services', 'commandes.categorie_service_id', '=', 'categorie_services.id')
            ->select('categorie_services.nom', DB::raw('count(*) as total'))
            ->groupBy('categorie_services.nom')
            ->get();

        // Commandes récentes
        $commandes_recentes = Commande::with(['client.user', 'prestataire.user', 'categorieService'])
            ->latest()
            ->limit(10)
            ->get();

        // Prestataires en attente de validation
        $prestataires_en_attente = Prestataire::with('user')
            ->where('statut_inscription', 'en_attente')
            ->latest()
            ->limit(5)
            ->get();

        // Prestataires avec solde négatif
        $prestataires_solde_negatif = Prestataire::with('user')
            ->where('solde', '<', 0)
            ->orderBy('solde', 'asc')
            ->limit(5)
            ->get();

        return view('admin.dashboard.index', compact(
            'stats',
            'evolution_users',
            'evolution_commandes',
            'evolution_revenus',
            'commandes_par_statut',
            'commandes_par_categorie',
            'commandes_recentes',
            'prestataires_en_attente',
            'prestataires_solde_negatif'
        ));
    }
}
