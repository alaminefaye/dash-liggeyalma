<?php

namespace App\Exports;

use App\Models\Transaction;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class TransactionsExport implements FromCollection, WithHeadings, WithMapping
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return Transaction::with(['client.user', 'prestataire.user', 'commande'])->get();
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'ID',
            'Type',
            'Client',
            'Prestataire',
            'Commande ID',
            'Montant',
            'Commission',
            'Méthode Paiement',
            'Statut',
            'Référence Externe',
            'Date',
        ];
    }

    /**
     * @param Transaction $transaction
     * @return array
     */
    public function map($transaction): array
    {
        return [
            $transaction->id,
            ucfirst($transaction->type ?? 'N/A'),
            $transaction->client->user->name ?? 'N/A',
            $transaction->prestataire->user->name ?? 'N/A',
            $transaction->commande_id ?? 'N/A',
            number_format($transaction->montant ?? 0, 0, ',', ' ') . ' FCFA',
            number_format($transaction->commission ?? 0, 0, ',', ' ') . ' FCFA',
            ucfirst(str_replace('_', ' ', $transaction->methode_paiement ?? 'N/A')),
            ucfirst(str_replace('_', ' ', $transaction->statut ?? 'N/A')),
            $transaction->reference_externe ?? 'N/A',
            $transaction->created_at->format('d/m/Y H:i'),
        ];
    }
}
