<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CategorieService;
use App\Models\Transaction;
use App\Models\Prestataire;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CommissionController extends Controller
{
    /**
     * Configuration des commissions
     */
    public function index()
    {
        $categories = CategorieService::all();
        $defaultRate = DB::table('parametres')->where('cle', 'commission_par_defaut')->value('valeur') ?? 10;
        
        return view('admin.commissions.index', compact('categories', 'defaultRate'));
    }

    /**
     * Mettre à jour les taux de commission
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'default_rate' => 'required|numeric|min:0|max:100',
            'categories' => 'required|array',
            'categories.*' => 'numeric|min:0|max:100',
        ]);

        DB::transaction(function() use ($validated) {
            // Mettre à jour le taux par défaut
            DB::table('parametres')->updateOrInsert(
                ['cle' => 'commission_par_defaut'],
                ['valeur' => $validated['default_rate'], 'type' => 'integer', 'groupe' => 'commission']
            );

            // Mettre à jour les taux par catégorie
            foreach ($validated['categories'] as $categoryId => $rate) {
                CategorieService::where('id', $categoryId)->update([
                    'commission_rate' => $rate
                ]);
            }
        });

        return redirect()->back()
            ->with('success', 'Taux de commission mis à jour avec succès.');
    }

    /**
     * Statistiques des commissions
     */
    public function statistics(Request $request)
    {
        $dateFrom = $request->input('date_from', now()->startOfMonth());
        $dateTo = $request->input('date_to', now()->endOfMonth());

        $stats = [
            'total' => Transaction::where('type', 'commission')
                ->whereBetween('created_at', [$dateFrom, $dateTo])
                ->sum('montant'),
            'par_categorie' => DB::table('transactions')
                ->join('commandes', 'transactions.commande_id', '=', 'commandes.id')
                ->join('categorie_services', 'commandes.categorie_service_id', '=', 'categorie_services.id')
                ->where('transactions.type', 'commission')
                ->whereBetween('transactions.created_at', [$dateFrom, $dateTo])
                ->select('categorie_services.nom', DB::raw('SUM(transactions.montant) as total'))
                ->groupBy('categorie_services.nom')
                ->get(),
            'commission_due' => Transaction::where('type', 'commission')
                ->where('statut', 'en_attente')
                ->sum('montant'),
            'commission_payee' => Transaction::where('type', 'commission')
                ->where('statut', 'validee')
                ->whereBetween('created_at', [$dateFrom, $dateTo])
                ->sum('montant'),
        ];

        return view('admin.commissions.statistics', compact('stats', 'dateFrom', 'dateTo'));
    }

    /**
     * Rapports des commissions
     */
    public function reports(Request $request)
    {
        $dateFrom = $request->input('date_from', now()->startOfMonth());
        $dateTo = $request->input('date_to', now()->endOfMonth());

        // Prestataires avec solde négatif
        $prestatairesSoldeNegatif = Prestataire::where('solde', '<', 0)
            ->with('user')
            ->orderBy('solde', 'asc')
            ->get();

        // Commission due vs payée
        $commissionDue = Transaction::where('type', 'commission')
            ->where('statut', 'en_attente')
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->sum('montant');

        $commissionPayee = Transaction::where('type', 'commission')
            ->where('statut', 'validee')
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->sum('montant');

        $ecart = $commissionDue - $commissionPayee;

        return view('admin.commissions.reports', compact(
            'prestatairesSoldeNegatif',
            'commissionDue',
            'commissionPayee',
            'ecart',
            'dateFrom',
            'dateTo'
        ));
    }
}
