<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Avis extends Model
{
    protected $fillable = [
        'commande_id',
        'client_id',
        'prestataire_id',
        'note',
        'commentaire',
        'photos',
        'criteres',
        'reponse_prestataire',
        'date_reponse',
    ];

    protected $casts = [
        'photos' => 'array',
        'criteres' => 'array',
        'date_reponse' => 'datetime',
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
