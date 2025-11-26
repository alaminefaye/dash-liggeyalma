<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    protected $fillable = [
        'commande_id',
        'client_id',
        'prestataire_id',
        'type',
        'montant',
        'commission',
        'methode_paiement',
        'statut',
        'reference_externe',
        'notes',
    ];

    protected $casts = [
        'montant' => 'decimal:2',
        'commission' => 'decimal:2',
    ];

    /**
     * Relation avec Commande
     */
    public function commande(): BelongsTo
    {
        return $this->belongsTo(Commande::class);
    }

    /**
     * Relation avec Client
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Relation avec Prestataire
     */
    public function prestataire(): BelongsTo
    {
        return $this->belongsTo(Prestataire::class);
    }
}
