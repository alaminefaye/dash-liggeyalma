<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Exports\TransactionsExport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class TransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Transaction::with(['client.user', 'prestataire.user', 'commande']);

        // Recherche
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('id', $search)
                  ->orWhere('reference_externe', 'like', "%{$search}%")
                  ->orWhereHas('client.user', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                  })
                  ->orWhereHas('prestataire.user', function($q) use ($search) {
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

        // Filtre par méthode de paiement
        if ($request->filled('methode_paiement')) {
            $query->where('methode_paiement', $request->methode_paiement);
        }

        // Filtre par date
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $transactions = $query->latest()->paginate(15);

        // Statistiques
        $stats = [
            'total' => Transaction::count(),
            'paiements' => Transaction::where('type', 'paiement')->sum('montant'),
            'commissions' => Transaction::where('type', 'commission')->sum('montant'),
            'retraits' => Transaction::where('type', 'retrait')->sum('montant'),
        ];

        return view('admin.transactions.index', compact('transactions', 'stats'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Transaction $transaction)
    {
        $transaction->load(['client.user', 'prestataire.user', 'commande']);
        return view('admin.transactions.show', compact('transaction'));
    }

    /**
     * Valider une transaction
     */
    public function validate(Transaction $transaction)
    {
        if ($transaction->statut === 'validee') {
            return redirect()->back()
                ->with('error', 'Cette transaction est déjà validée.');
        }

        $transaction->update(['statut' => 'validee']);

        // Si c'est un paiement, créditer le prestataire
        if ($transaction->type === 'paiement' && $transaction->prestataire_id) {
            $montantNet = $transaction->montant - $transaction->commission;
            $transaction->prestataire->increment('solde', $montantNet);
        }

        return redirect()->back()
            ->with('success', 'Transaction validée avec succès.');
    }

    /**
     * Refuser une transaction
     */
    public function reject(Request $request, Transaction $transaction)
    {
        $validated = $request->validate([
            'motif' => 'required|string|max:500',
        ]);

        $transaction->update([
            'statut' => 'refusee',
            'notes' => $validated['motif'],
        ]);

        return redirect()->back()
            ->with('success', 'Transaction refusée.');
    }

    /**
     * Rembourser une transaction
     */
    public function refund(Transaction $transaction)
    {
        if ($transaction->statut !== 'validee') {
            return redirect()->back()
                ->with('error', 'Seules les transactions validées peuvent être remboursées.');
        }

        // Logique de remboursement à implémenter selon la méthode de paiement
        $transaction->update([
            'statut' => 'refusee',
            'notes' => 'Remboursement effectué le ' . now()->format('d/m/Y H:i'),
        ]);

        return redirect()->back()
            ->with('success', 'Remboursement initié.');
    }
}
