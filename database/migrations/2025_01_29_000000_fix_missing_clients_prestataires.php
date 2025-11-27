<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Client;
use App\Models\Prestataire;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Cette migration corrige les utilisateurs existants qui n'ont pas
     * d'enregistrement correspondant dans clients ou prestataires
     */
    public function up(): void
    {
        // Récupérer les utilisateurs qui n'ont ni Client ni Prestataire
        $userModels = User::whereIn('role', ['client', 'prestataire'])
            ->whereDoesntHave('client')
            ->whereDoesntHave('prestataire')
            ->get();

        foreach ($userModels as $user) {
            if ($user->role === 'prestataire') {
                // Vérifier si un Prestataire existe déjà (au cas où)
                $existingPrestataire = Prestataire::where('user_id', $user->id)->first();
                if (!$existingPrestataire) {
                    Prestataire::create([
                        'user_id' => $user->id,
                        'statut_inscription' => 'en_attente', // À valider par l'admin
                        'solde' => 0,
                        'score_confiance' => 0,
                        'disponible' => false,
                    ]);
                }
            } else {
                // Vérifier si un Client existe déjà (au cas où)
                $existingClient = Client::where('user_id', $user->id)->first();
                if (!$existingClient) {
                    Client::create([
                        'user_id' => $user->id,
                        'score_confiance' => 0,
                    ]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Cette migration ne peut pas être annulée car elle corrige des données
        // On ne supprime pas les enregistrements créés
    }
};

