<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Contournement extends Model
{
    protected $fillable = [
        'prestataire_id',
        'client_id',
        'commande_id',
        'type',
        'description',
        'preuves',
        'statut',
        'sanction_appliquee',
    ];

    protected $casts = [
        'preuves' => 'array',
    ];

    /**
     * Relation avec Prestataire
     */
    public function prestataire(): BelongsTo
    {
        return $this->belongsTo(Prestataire::class);
    }

    /**
     * Relation avec Client
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Relation avec Commande
     */
    public function commande(): BelongsTo
    {
        return $this->belongsTo(Commande::class);
    }
}
