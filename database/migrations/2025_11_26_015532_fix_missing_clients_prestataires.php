<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

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
        // Vérifier que les tables clients et prestataires existent avant de continuer
        if (!Schema::hasTable('clients') || !Schema::hasTable('prestataires')) {
            // Les tables n'existent pas encore, on sort de la migration
            // Cette migration sera exécutée après la création des tables
            return;
        }

        // Utiliser des requêtes SQL directes pour éviter les problèmes de relations Eloquent
        // Récupérer les utilisateurs clients qui n'ont pas d'enregistrement dans clients
        $clientsWithoutRecord = DB::table('users')
            ->where('role', 'client')
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('clients')
                    ->whereColumn('clients.user_id', 'users.id');
            })
            ->get();

        // Créer les enregistrements clients manquants
        foreach ($clientsWithoutRecord as $user) {
            DB::table('clients')->insert([
                'user_id' => $user->id,
                'score_confiance' => 0.00,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Récupérer les utilisateurs prestataires qui n'ont pas d'enregistrement dans prestataires
        $prestatairesWithoutRecord = DB::table('users')
            ->where('role', 'prestataire')
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('prestataires')
                    ->whereColumn('prestataires.user_id', 'users.id');
            })
            ->get();

        // Créer les enregistrements prestataires manquants
        foreach ($prestatairesWithoutRecord as $user) {
            // Vérifier si le champ metier est nullable ou non
            $metierValue = 'Non spécifié'; // Valeur par défaut
            
            DB::table('prestataires')->insert([
                'user_id' => $user->id,
                'metier' => $metierValue,
                'statut_inscription' => 'en_attente',
                'solde' => 0,
                'score_confiance' => 0.00,
                'disponible' => false,
                'frais_deplacement' => 0,
                'rayon_intervention' => 10,
                'annees_experience' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
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

