<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create admin user (only if doesn't exist)
        User::firstOrCreate(
            ['email' => 'admin@admin.com'],
            [
                'name' => 'Admin User',
                'password' => bcrypt('password'),
                'role' => 'admin',
                'status' => 'active',
            ]
        );
        
        // Seed categories with subcategories
        $this->call([
            CategorieServiceSeeder::class,
        ]);
        
        // Seed clients
        $this->call([
            ClientSeeder::class,
        ]);
        
        // Seed prestataires
        $this->call([
            PrestataireSeeder::class,
        ]);
    }
}
