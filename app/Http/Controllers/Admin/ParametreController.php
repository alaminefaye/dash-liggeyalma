<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ParametreController extends Controller
{
    /**
     * Display settings page
     */
    public function index()
    {
        $parametres = DB::table('parametres')
            ->orderBy('groupe')
            ->orderBy('cle')
            ->get()
            ->groupBy('groupe');

        return view('admin.parametres.index', compact('parametres'));
    }

    /**
     * Update settings
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'parametres' => 'required|array',
        ]);

        DB::transaction(function() use ($validated) {
            foreach ($validated['parametres'] as $cle => $valeur) {
                DB::table('parametres')
                    ->where('cle', $cle)
                    ->update(['valeur' => $valeur]);
            }
        });

        return redirect()->back()
            ->with('success', 'Paramètres mis à jour avec succès.');
    }

    /**
     * Initialize default settings
     */
    public function initialize()
    {
        $defaults = [
            // Général
            ['cle' => 'nom_application', 'valeur' => 'Liggeyalma', 'type' => 'string', 'groupe' => 'general', 'description' => 'Nom de l\'application'],
            ['cle' => 'email_contact', 'valeur' => 'contact@liggeyalma.com', 'type' => 'string', 'groupe' => 'general', 'description' => 'Email de contact'],
            ['cle' => 'telephone_contact', 'valeur' => '+221771234567', 'type' => 'string', 'groupe' => 'general', 'description' => 'Téléphone de contact'],
            
            // Commission
            ['cle' => 'commission_par_defaut', 'valeur' => '10', 'type' => 'integer', 'groupe' => 'commission', 'description' => 'Taux de commission par défaut (%)'],
            ['cle' => 'commission_minimum', 'valeur' => '5', 'type' => 'integer', 'groupe' => 'commission', 'description' => 'Taux de commission minimum (%)'],
            ['cle' => 'commission_maximum', 'valeur' => '20', 'type' => 'integer', 'groupe' => 'commission', 'description' => 'Taux de commission maximum (%)'],
            
            // Paiement
            ['cle' => 'frais_retrait', 'valeur' => '500', 'type' => 'integer', 'groupe' => 'paiement', 'description' => 'Frais de retrait (FCFA)'],
            ['cle' => 'montant_retrait_minimum', 'valeur' => '5000', 'type' => 'integer', 'groupe' => 'paiement', 'description' => 'Montant minimum pour retrait (FCFA)'],
            ['cle' => 'delai_retrait', 'valeur' => '48', 'type' => 'integer', 'groupe' => 'paiement', 'description' => 'Délai de traitement retrait (heures)'],
            
            // Notifications
            ['cle' => 'notifications_email', 'valeur' => '1', 'type' => 'boolean', 'groupe' => 'notification', 'description' => 'Activer les notifications email'],
            ['cle' => 'notifications_sms', 'valeur' => '1', 'type' => 'boolean', 'groupe' => 'notification', 'description' => 'Activer les notifications SMS'],
            
            // Anti-contournement
            ['cle' => 'detection_contournement_active', 'valeur' => '1', 'type' => 'boolean', 'groupe' => 'securite', 'description' => 'Activer la détection de contournement'],
            ['cle' => 'blocage_automatique', 'valeur' => '1', 'type' => 'boolean', 'groupe' => 'securite', 'description' => 'Blocage automatique après 3 contournements'],
        ];

        foreach ($defaults as $param) {
            DB::table('parametres')->updateOrInsert(
                ['cle' => $param['cle']],
                $param
            );
        }

        return redirect()->route('admin.parametres.index')
            ->with('success', 'Paramètres initialisés avec succès.');
    }

    /**
     * Paramètres Anti-Contournement
     */
    public function antiContournement()
    {
        $parametres = DB::table('parametres')
            ->where('groupe', 'securite')
            ->orWhere('groupe', 'anti-contournement')
            ->get()
            ->keyBy('cle');

        return view('admin.parametres.anti-contournement', compact('parametres'));
    }

    /**
     * Update Anti-Contournement settings
     */
    public function updateAntiContournement(Request $request)
    {
        $validated = $request->validate([
            'detection_contournement_active' => 'nullable|boolean',
            'blocage_automatique' => 'nullable|boolean',
            'nombre_contournements_avant_blocage' => 'nullable|integer|min:1|max:10',
            'seuil_solde_negatif' => 'nullable|numeric|min:0',
            'appel_masque_active' => 'nullable|boolean',
            'partage_numero_active' => 'nullable|boolean',
            'commission_par_defaut' => 'nullable|numeric|min:0|max:100',
        ]);

        DB::transaction(function() use ($validated) {
            foreach ($validated as $cle => $valeur) {
                DB::table('parametres')->updateOrInsert(
                    ['cle' => $cle],
                    [
                        'valeur' => $valeur ?? '0',
                        'type' => is_bool($valeur) ? 'boolean' : (is_numeric($valeur) ? 'numeric' : 'string'),
                        'groupe' => 'anti-contournement',
                    ]
                );
            }
        });

        return redirect()->back()
            ->with('success', 'Paramètres anti-contournement mis à jour avec succès.');
    }
}
