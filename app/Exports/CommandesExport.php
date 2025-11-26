<?php

namespace App\Exports;

use App\Models\Commande;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class CommandesExport implements FromCollection, WithHeadings, WithMapping
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return Commande::with(['client.user', 'prestataire.user', 'sousCategorieService.categorieService'])->get();
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'ID',
            'Client',
            'Prestataire',
            'Service',
            'Statut',
            'Montant Total',
            'Commission',
            'Méthode Paiement',
            'Statut Paiement',
            'Date Création',
        ];
    }

    /**
     * @param Commande $commande
     * @return array
     */
    public function map($commande): array
    {
        return [
            $commande->id,
            $commande->client->user->name ?? 'N/A',
            $commande->prestataire->user->name ?? 'En attente',
            $commande->sousCategorieService->categorieService->nom ?? 'N/A',
            ucfirst(str_replace('_', ' ', $commande->statut ?? 'N/A')),
            number_format($commande->montant_total ?? 0, 0, ',', ' ') . ' FCFA',
            number_format($commande->montant_commission ?? 0, 0, ',', ' ') . ' FCFA',
            ucfirst(str_replace('_', ' ', $commande->methode_paiement ?? 'N/A')),
            ucfirst(str_replace('_', ' ', $commande->statut_paiement ?? 'N/A')),
            $commande->created_at->format('d/m/Y H:i'),
        ];
    }
}
