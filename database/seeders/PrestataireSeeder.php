<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Prestataire;
use Illuminate\Support\Facades\Hash;

class PrestataireSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $prestataires = [
            [
                'name' => 'Amadou Ndiaye',
                'email' => 'amadou.plombier@example.com',
                'phone' => '+221771234570',
                'password' => 'password',
                'metier' => 'Plombier',
                'specialites' => ['Débouchage', 'Réparation de fuite'],
                'description' => 'Plombier expérimenté avec 10 ans d\'expérience',
                'annees_experience' => 10,
                'tarif_horaire' => 5000,
                'frais_deplacement' => 2000,
                'statut_inscription' => 'valide',
                'solde' => 50000,
            ],
            [
                'name' => 'Fatou Diop',
                'email' => 'fatou.electricien@example.com',
                'phone' => '+221771234571',
                'password' => 'password',
                'metier' => 'Électricien',
                'specialites' => ['Installation', 'Dépannage'],
                'description' => 'Électricien certifié, spécialisé en installations résidentielles',
                'annees_experience' => 8,
                'tarif_horaire' => 6000,
                'frais_deplacement' => 2000,
                'statut_inscription' => 'valide',
                'solde' => 75000,
            ],
            [
                'name' => 'Mamadou Fall',
                'email' => 'mamadou.mecanicien@example.com',
                'phone' => '+221771234572',
                'password' => 'password',
                'metier' => 'Mécanicien Auto',
                'specialites' => ['Réparation moteur', 'Vidange'],
                'description' => 'Mécanicien automobile professionnel',
                'annees_experience' => 15,
                'tarif_horaire' => 8000,
                'frais_deplacement' => 3000,
                'statut_inscription' => 'en_attente',
                'solde' => 0,
            ],
            [
                'name' => 'Awa Sène',
                'email' => 'awa.jardiniere@example.com',
                'phone' => '+221771234573',
                'password' => 'password',
                'metier' => 'Jardinier',
                'specialites' => ['Tonte', 'Plantation'],
                'description' => 'Jardinier paysagiste',
                'annees_experience' => 5,
                'tarif_horaire' => 4000,
                'frais_deplacement' => 1500,
                'statut_inscription' => 'valide',
                'solde' => 30000,
            ],
        ];

        foreach ($prestataires as $prestataireData) {
            $user = User::firstOrCreate(
                ['email' => $prestataireData['email']],
                [
                    'name' => $prestataireData['name'],
                    'phone' => $prestataireData['phone'],
                    'password' => Hash::make($prestataireData['password']),
                    'role' => 'prestataire',
                    'status' => 'active',
                ]
            );

            Prestataire::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'metier' => $prestataireData['metier'],
                    'specialites' => $prestataireData['specialites'],
                    'description' => $prestataireData['description'],
                    'annees_experience' => $prestataireData['annees_experience'],
                    'tarif_horaire' => $prestataireData['tarif_horaire'],
                    'frais_deplacement' => $prestataireData['frais_deplacement'],
                    'statut_inscription' => $prestataireData['statut_inscription'],
                    'solde' => $prestataireData['solde'],
                    'score_confiance' => 5.0,
                    'disponible' => $prestataireData['statut_inscription'] === 'valide',
                ]
            );
        }
    }
}
