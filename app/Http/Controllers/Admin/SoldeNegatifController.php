<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Prestataire;
use Illuminate\Http\Request;

class SoldeNegatifController extends Controller
{
    /**
     * Liste des prestataires avec solde négatif
     */
    public function index()
    {
        $prestataires = Prestataire::with('user')
            ->where('solde', '<', 0)
            ->orderBy('solde', 'asc')
            ->paginate(15);

        $totalDette = Prestataire::where('solde', '<', 0)->sum('solde');

        return view('admin.soldes-negatifs.index', compact('prestataires', 'totalDette'));
    }

    /**
     * Détails d'un prestataire avec solde négatif
     */
    public function show(Prestataire $prestataire)
    {
        $prestataire->load(['user', 'transactions', 'commandes']);
        
        $stats = [
            'dette' => abs($prestataire->solde),
            'transactions' => $prestataire->transactions()->where('type', 'commission')->sum('montant'),
            'commandes_total' => $prestataire->commandes()->count(),
        ];

        return view('admin.soldes-negatifs.show', compact('prestataire', 'stats'));
    }

    /**
     * Forcer le paiement de la commission
     */
    public function forcePayment(Prestataire $prestataire)
    {
        // Logique pour forcer le paiement
        // Peut bloquer le compte jusqu'au paiement
        
        return redirect()->back()
            ->with('success', 'Action de paiement forcé initiée.');
    }

    /**
     * Bloquer le compte jusqu'au paiement
     */
    public function block(Prestataire $prestataire)
    {
        $prestataire->user->update(['status' => 'blocked']);

        return redirect()->back()
            ->with('success', 'Compte bloqué jusqu\'au paiement de la commission.');
    }

    /**
     * Envoyer un rappel
     */
    public function sendReminder(Prestataire $prestataire)
    {
        // Logique pour envoyer un rappel (email/SMS)
        
        return redirect()->back()
            ->with('success', 'Rappel envoyé au prestataire.');
    }
}
