<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Contournement;
use App\Models\Prestataire;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AntiFraudeController extends Controller
{
    /**
     * Statistiques anti-fraude
     */
    public function statistics()
    {
        // Statistiques globales
        $stats = [
            'total_contournements' => Contournement::count(),
            'contournements_detectes' => Contournement::where('statut', 'detecte')->count(),
            'contournements_confirmes' => Contournement::where('statut', 'confirme')->count(),
            'prestataires_bloques' => Prestataire::whereHas('user', function($q) {
                $q->where('status', 'blocked');
            })->count(),
        ];

        // Taux de contournement par prestataire
        $tauxContournement = DB::table('contournements')
            ->join('prestataires', 'contournements.prestataire_id', '=', 'prestataires.id')
            ->join('users', 'prestataires.user_id', '=', 'users.id')
            ->select('users.name', 'prestataires.id', DB::raw('COUNT(*) as total'))
            ->where('contournements.statut', 'confirme')
            ->groupBy('prestataires.id', 'users.name')
            ->orderBy('total', 'desc')
            ->limit(10)
            ->get();

        // Taux de commission réel vs théorique
        $commissionTheorique = Transaction::where('type', 'commission')
            ->where('statut', 'validee')
            ->sum('montant');

        $commissionReelle = Transaction::where('type', 'paiement')
            ->where('statut', 'validee')
            ->sum('montant') * 0.10; // Exemple : 10% de commission

        $ecartCommission = $commissionTheorique - $commissionReelle;

        // Évolution des contournements (7 derniers jours)
        $evolutionContournements = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $evolutionContournements[] = [
                'date' => $date->format('Y-m-d'),
                'label' => $date->format('d/m'),
                'total' => Contournement::whereDate('created_at', $date)->count(),
            ];
        }

        return view('admin.anti-fraude.statistiques', compact(
            'stats',
            'tauxContournement',
            'commissionTheorique',
            'commissionReelle',
            'ecartCommission',
            'evolutionContournements'
        ));
    }
}
