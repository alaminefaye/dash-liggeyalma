<?php

namespace App\Exports;

use App\Models\Client;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ClientsExport implements FromCollection, WithHeadings, WithMapping
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return Client::with('user')->get();
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'ID',
            'Nom',
            'Email',
            'Téléphone',
            'Adresse',
            'Ville',
            'Pays',
            'Statut',
            'Date d\'inscription',
        ];
    }

    /**
     * @param Client $client
     * @return array
     */
    public function map($client): array
    {
        return [
            $client->id,
            $client->user->name ?? 'N/A',
            $client->user->email ?? 'N/A',
            $client->user->phone ?? 'N/A',
            $client->address ?? 'N/A',
            $client->city ?? 'N/A',
            $client->country ?? 'N/A',
            $client->user->status ?? 'N/A',
            $client->created_at->format('d/m/Y H:i'),
        ];
    }
}
