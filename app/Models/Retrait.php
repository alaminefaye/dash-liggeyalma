<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Retrait extends Model
{
    protected $fillable = [
        'prestataire_id',
        'montant',
        'frais_retrait',
        'montant_net',
        'methode',
        'numero_compte',
        'statut',
        'motif_refus',
        'date_validation',
        'valide_par',
    ];

    protected $casts = [
        'montant' => 'decimal:2',
        'frais_retrait' => 'decimal:2',
        'montant_net' => 'decimal:2',
        'date_validation' => 'datetime',
    ];

    /**
     * Relation avec Prestataire
     */
    public function prestataire(): BelongsTo
    {
        return $this->belongsTo(Prestataire::class);
    }

    /**
     * Relation avec User (Admin qui a validé)
     */
    public function validePar(): BelongsTo
    {
        return $this->belongsTo(User::class, 'valide_par');
    }
}
