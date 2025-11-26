<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\CategorieService;
use App\Models\SousCategorieService;

class CategorieServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Plomberie
        $plomberie = CategorieService::firstOrCreate(
            ['nom' => 'Plomberie'],
            [
                'description' => 'Services de plomberie et réparation',
                'icone' => 'bx-wrench',
                'couleur' => '#696cff',
                'prix_fixe' => true,
                'commission_rate' => 10.00,
                'active' => true,
            ]
        );

        SousCategorieService::firstOrCreate(
            ['categorie_service_id' => $plomberie->id, 'nom' => 'Débouchage'],
            [
                'description' => 'Débouchage de canalisations',
                'prix_fixe' => 8000,
                'active' => true,
            ]
        );

        SousCategorieService::firstOrCreate(
            ['categorie_service_id' => $plomberie->id, 'nom' => 'Réparation de fuite'],
            [
                'description' => 'Réparation de fuites d\'eau',
                'prix_fixe' => 12000,
                'active' => true,
            ]
        );

        SousCategorieService::firstOrCreate(
            ['categorie_service_id' => $plomberie->id, 'nom' => 'Installation sanitaire'],
            [
                'description' => 'Installation de robinets, douches, etc.',
                'prix_fixe' => 15000,
                'active' => true,
            ]
        );

        // Électricité
        $electricite = CategorieService::firstOrCreate(
            ['nom' => 'Électricité'],
            [
                'description' => 'Services électriques et installations',
                'icone' => 'bx-bolt',
                'couleur' => '#ffab00',
                'prix_fixe' => true,
                'commission_rate' => 10.00,
                'active' => true,
            ]
        );

        SousCategorieService::firstOrCreate(
            ['categorie_service_id' => $electricite->id, 'nom' => 'Installation prise électrique'],
            [
                'description' => 'Installation de prises électriques',
                'prix_fixe' => 6000,
                'active' => true,
            ]
        );

        SousCategorieService::firstOrCreate(
            ['categorie_service_id' => $electricite->id, 'nom' => 'Dépannage électrique'],
            [
                'description' => 'Réparation de pannes électriques',
                'prix_fixe' => 10000,
                'active' => true,
            ]
        );

        // Mécanique
        CategorieService::firstOrCreate(
            ['nom' => 'Mécanique Auto'],
            [
                'description' => 'Services de mécanique automobile',
                'icone' => 'bx-car',
                'couleur' => '#ff3e1d',
                'prix_fixe' => false,
                'commission_rate' => 12.00,
                'active' => true,
            ]
        );

        // Jardinage
        CategorieService::firstOrCreate(
            ['nom' => 'Jardinage'],
            [
                'description' => 'Services de jardinage et entretien',
                'icone' => 'bx-leaf',
                'couleur' => '#71dd37',
                'prix_fixe' => false,
                'commission_rate' => 10.00,
                'active' => true,
            ]
        );

        // Peinture
        CategorieService::firstOrCreate(
            ['nom' => 'Peinture'],
            [
                'description' => 'Services de peinture intérieure et extérieure',
                'icone' => 'bx-paint',
                'couleur' => '#ffab00',
                'prix_fixe' => false,
                'commission_rate' => 10.00,
                'active' => true,
            ]
        );
    }
}
