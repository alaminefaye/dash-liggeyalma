<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Client;
use Illuminate\Support\Facades\Hash;

class ClientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $clients = [
            [
                'name' => 'Moussa Diallo',
                'email' => 'moussa@example.com',
                'phone' => '+221771234567',
                'password' => 'password',
                'address' => 'Almadies, Dakar',
            ],
            [
                'name' => 'Aissatou Ba',
                'email' => 'aissatou@example.com',
                'phone' => '+221771234568',
                'password' => 'password',
                'address' => 'Plateau, Dakar',
            ],
            [
                'name' => 'Ibrahima Sarr',
                'email' => 'ibrahima@example.com',
                'phone' => '+221771234569',
                'password' => 'password',
                'address' => 'Ouakam, Dakar',
            ],
        ];

        foreach ($clients as $clientData) {
            $user = User::firstOrCreate(
                ['email' => $clientData['email']],
                [
                    'name' => $clientData['name'],
                    'phone' => $clientData['phone'],
                    'password' => Hash::make($clientData['password']),
                    'role' => 'client',
                    'status' => 'active',
                ]
            );

            Client::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'address' => $clientData['address'],
                    'score_confiance' => 5.0,
                ]
            );
        }
    }
}
