<?php

namespace App\Exports;

use App\Models\Prestataire;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class PrestatairesExport implements FromCollection, WithHeadings, WithMapping
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return Prestataire::with('user')->get();
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
            'Métier',
            'Spécialités',
            'Tarif Horaire',
            'Solde',
            'Score Confiance',
            'Statut Inscription',
            'Statut Compte',
            'Date d\'inscription',
        ];
    }

    /**
     * @param Prestataire $prestataire
     * @return array
     */
    public function map($prestataire): array
    {
        return [
            $prestataire->id,
            $prestataire->user->name ?? 'N/A',
            $prestataire->user->email ?? 'N/A',
            $prestataire->user->phone ?? 'N/A',
            $prestataire->metier ?? 'N/A',
            is_array($prestataire->specialites) ? implode(', ', $prestataire->specialites) : 'N/A',
            number_format($prestataire->tarif_horaire ?? 0, 0, ',', ' ') . ' FCFA',
            number_format($prestataire->solde ?? 0, 0, ',', ' ') . ' FCFA',
            $prestataire->score_confiance ?? 0,
            $prestataire->statut_inscription ?? 'N/A',
            $prestataire->user->status ?? 'N/A',
            $prestataire->created_at->format('d/m/Y H:i'),
        ];
    }
}
