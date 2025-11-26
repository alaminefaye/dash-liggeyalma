<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Prestataire;
use App\Models\Retrait;
use App\Models\Contournement;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Liste des notifications
     */
    public function index()
    {
        $notifications = [];

        // Demandes d'inscription en attente
        $prestatairesEnAttente = Prestataire::where('statut_inscription', 'en_attente')
            ->with('user')
            ->latest()
            ->get();

        foreach ($prestatairesEnAttente as $prestataire) {
            $notifications[] = [
                'type' => 'prestataire_attente',
                'title' => 'Nouvelle demande d\'inscription',
                'message' => $prestataire->user->name . ' a demandé à devenir prestataire',
                'url' => route('admin.prestataires.show', $prestataire),
                'date' => $prestataire->created_at,
                'priority' => 'high',
            ];
        }

        // Retraits en attente
        $retraitsEnAttente = Retrait::where('statut', 'en_attente')
            ->with('prestataire.user')
            ->latest()
            ->get();

        foreach ($retraitsEnAttente as $retrait) {
            $notifications[] = [
                'type' => 'retrait_attente',
                'title' => 'Retrait en attente',
                'message' => $retrait->prestataire->user->name . ' demande un retrait de ' . number_format($retrait->montant, 0, ',', ' ') . ' FCFA',
                'url' => route('admin.retraits.show', $retrait),
                'date' => $retrait->created_at,
                'priority' => 'medium',
            ];
        }

        // Tentatives de contournement détectées
        $contournementsDetectes = Contournement::where('statut', 'detecte')
            ->with('prestataire.user')
            ->latest()
            ->get();

        foreach ($contournementsDetectes as $contournement) {
            $notifications[] = [
                'type' => 'contournement',
                'title' => 'Tentative de contournement détectée',
                'message' => 'Contournement détecté pour ' . $contournement->prestataire->user->name,
                'url' => route('admin.contournements.show', $contournement),
                'date' => $contournement->created_at,
                'priority' => 'high',
            ];
        }

        // Prestataires avec solde négatif
        $prestatairesSoldeNegatif = Prestataire::where('solde', '<', 0)
            ->with('user')
            ->orderBy('solde', 'asc')
            ->limit(5)
            ->get();

        foreach ($prestatairesSoldeNegatif as $prestataire) {
            $notifications[] = [
                'type' => 'solde_negatif',
                'title' => 'Solde négatif',
                'message' => $prestataire->user->name . ' a un solde de ' . number_format($prestataire->solde, 0, ',', ' ') . ' FCFA',
                'url' => route('admin.prestataires.show', $prestataire),
                'date' => now(),
                'priority' => 'high',
            ];
        }

        // Trier par date (plus récent en premier)
        usort($notifications, function($a, $b) {
            return $b['date'] <=> $a['date'];
        });

        return view('admin.notifications.index', compact('notifications'));
    }

    /**
     * Marquer une notification comme lue
     */
    public function markAsRead($id)
    {
        // Logique pour marquer comme lu (si vous avez une table notifications)
        return redirect()->back()
            ->with('success', 'Notification marquée comme lue.');
    }

    /**
     * Traiter une notification directement
     */
    public function handle(Request $request, $id)
    {
        $type = $request->input('type');
        $notificationId = $request->input('notification_id');

        // Rediriger vers la page appropriée selon le type
        switch ($type) {
            case 'prestataire_attente':
                return redirect()->route('admin.prestataires.show', $notificationId);
            
            case 'retrait_attente':
                return redirect()->route('admin.retraits.show', $notificationId);
            
            case 'contournement':
                return redirect()->route('admin.contournements.show', $notificationId);
            
            case 'solde_negatif':
                return redirect()->route('admin.prestataires.show', $notificationId);
            
            default:
                return redirect()->back()
                    ->with('error', 'Type de notification non reconnu.');
        }
    }
}
