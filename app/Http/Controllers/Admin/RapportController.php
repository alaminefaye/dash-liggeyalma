<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Commande;
use App\Models\Transaction;
use App\Models\Client;
use App\Models\Prestataire;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RapportController extends Controller
{
    /**
     * Display reports dashboard
     */
    public function index()
    {
        return view('admin.rapports.index');
    }

    /**
     * Generate financial report
     */
    public function financier(Request $request)
    {
        $dateFrom = $request->input('date_from', now()->startOfMonth());
        $dateTo = $request->input('date_to', now()->endOfMonth());

        $stats = [
            'revenus_total' => Transaction::where('type', 'commission')
                ->whereBetween('created_at', [$dateFrom, $dateTo])
                ->sum('montant'),
            'paiements_total' => Transaction::where('type', 'paiement')
                ->whereBetween('created_at', [$dateFrom, $dateTo])
                ->sum('montant'),
            'retraits_total' => Transaction::where('type', 'retrait')
                ->whereBetween('created_at', [$dateFrom, $dateTo])
                ->sum('montant'),
            'commandes_total' => Commande::whereBetween('created_at', [$dateFrom, $dateTo])->count(),
            'commandes_terminees' => Commande::where('statut', 'terminee')
                ->whereBetween('created_at', [$dateFrom, $dateTo])
                ->count(),
        ];

        // Revenus par jour
        $revenusParJour = Transaction::where('type', 'commission')
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(montant) as total'))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return view('admin.rapports.financier', compact('stats', 'revenusParJour', 'dateFrom', 'dateTo'));
    }

    /**
     * Generate users report
     */
    public function utilisateurs(Request $request)
    {
        $dateFrom = $request->input('date_from', now()->subMonth());
        $dateTo = $request->input('date_to', now());

        $stats = [
            'clients_total' => Client::whereBetween('created_at', [$dateFrom, $dateTo])->count(),
            'prestataires_total' => Prestataire::whereBetween('created_at', [$dateFrom, $dateTo])->count(),
            'prestataires_valides' => Prestataire::where('statut_inscription', 'valide')
                ->whereBetween('created_at', [$dateFrom, $dateTo])
                ->count(),
            'prestataires_actifs' => Prestataire::where('disponible', true)
                ->whereBetween('created_at', [$dateFrom, $dateTo])
                ->count(),
        ];

        // Inscriptions par jour
        $inscriptionsParJour = DB::table('users')
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('role'), DB::raw('COUNT(*) as total'))
            ->groupBy('date', 'role')
            ->orderBy('date')
            ->get()
            ->groupBy('date');

        return view('admin.rapports.utilisateurs', compact('stats', 'inscriptionsParJour', 'dateFrom', 'dateTo'));
    }

    /**
     * Generate services report
     */
    public function services(Request $request)
    {
        $dateFrom = $request->input('date_from', now()->subMonth());
        $dateTo = $request->input('date_to', now());

        // Commandes par catégorie
        $commandesParCategorie = Commande::whereBetween('created_at', [$dateFrom, $dateTo])
            ->join('categorie_services', 'commandes.categorie_service_id', '=', 'categorie_services.id')
            ->select('categorie_services.nom', DB::raw('COUNT(*) as total'), DB::raw('SUM(montant_total) as revenus'))
            ->groupBy('categorie_services.nom')
            ->get();

        // Top prestataires
        $topPrestataires = Prestataire::with('user')
            ->whereHas('commandes', function($q) use ($dateFrom, $dateTo) {
                $q->where('statut', 'terminee')
                  ->whereBetween('created_at', [$dateFrom, $dateTo]);
            })
            ->withCount(['commandes' => function($q) use ($dateFrom, $dateTo) {
                $q->where('statut', 'terminee')
                  ->whereBetween('created_at', [$dateFrom, $dateTo]);
            }])
            ->orderBy('commandes_count', 'desc')
            ->take(10)
            ->get();

        return view('admin.rapports.services', compact('commandesParCategorie', 'topPrestataires', 'dateFrom', 'dateTo'));
    }

    /**
     * Generate commandes report
     */
    public function commandes(Request $request)
    {
        $dateFrom = $request->input('date_from', now()->subMonth());
        $dateTo = $request->input('date_to', now());

        // Commandes par période
        $commandesParPeriode = Commande::whereBetween('created_at', [$dateFrom, $dateTo])
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as total'))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Commandes par statut
        $commandesParStatut = Commande::whereBetween('created_at', [$dateFrom, $dateTo])
            ->select('statut', DB::raw('COUNT(*) as total'))
            ->groupBy('statut')
            ->get();

        // Commandes par catégorie
        $commandesParCategorie = Commande::whereBetween('created_at', [$dateFrom, $dateTo])
            ->join('categorie_services', 'commandes.categorie_service_id', '=', 'categorie_services.id')
            ->select('categorie_services.nom', DB::raw('COUNT(*) as total'))
            ->groupBy('categorie_services.nom')
            ->get();

        // Taux d'acceptation
        $totalCommandes = Commande::whereBetween('created_at', [$dateFrom, $dateTo])->count();
        $commandesAcceptees = Commande::whereIn('statut', ['acceptee', 'en_cours', 'terminee'])
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->count();
        $tauxAcceptation = $totalCommandes > 0 ? ($commandesAcceptees / $totalCommandes) * 100 : 0;

        // Taux d'annulation
        $commandesAnnulees = Commande::where('statut', 'annulee')
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->count();
        $tauxAnnulation = $totalCommandes > 0 ? ($commandesAnnulees / $totalCommandes) * 100 : 0;

        $stats = [
            'total' => $totalCommandes,
            'acceptees' => $commandesAcceptees,
            'annulees' => $commandesAnnulees,
            'taux_acceptation' => round($tauxAcceptation, 2),
            'taux_annulation' => round($tauxAnnulation, 2),
        ];

        return view('admin.rapports.commandes', compact(
            'commandesParPeriode',
            'commandesParStatut',
            'commandesParCategorie',
            'stats',
            'dateFrom',
            'dateTo'
        ));
    }

    /**
     * Export report to CSV
     */
    public function export(Request $request)
    {
        $type = $request->input('type', 'financier');
        $dateFrom = $request->input('date_from', now()->startOfMonth());
        $dateTo = $request->input('date_to', now()->endOfMonth());

        $filename = "rapport_{$type}_" . now()->format('Y-m-d') . ".csv";
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($type, $dateFrom, $dateTo) {
            $file = fopen('php://output', 'w');
            
            if ($type === 'financier') {
                fputcsv($file, ['Date', 'Type', 'Montant', 'Commission']);
                $transactions = Transaction::whereBetween('created_at', [$dateFrom, $dateTo])->get();
                foreach ($transactions as $transaction) {
                    fputcsv($file, [
                        $transaction->created_at->format('Y-m-d H:i'),
                        $transaction->type,
                        $transaction->montant,
                        $transaction->commission,
                    ]);
                }
            } elseif ($type === 'commandes') {
                fputcsv($file, ['ID', 'Date', 'Client', 'Prestataire', 'Montant', 'Statut']);
                $commandes = Commande::with(['client.user', 'prestataire.user'])
                    ->whereBetween('created_at', [$dateFrom, $dateTo])
                    ->get();
                foreach ($commandes as $commande) {
                    fputcsv($file, [
                        $commande->id,
                        $commande->created_at->format('Y-m-d H:i'),
                        $commande->client->user->name ?? 'N/A',
                        $commande->prestataire->user->name ?? 'N/A',
                        $commande->montant_total,
                        $commande->statut,
                    ]);
                }
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
