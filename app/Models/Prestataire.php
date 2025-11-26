<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Prestataire extends Model
{
    protected $fillable = [
        'user_id',
        'metier',
        'specialites',
        'description',
        'annees_experience',
        'tarif_horaire',
        'forfaits',
        'frais_deplacement',
        'zone_intervention',
        'latitude',
        'longitude',
        'rayon_intervention',
        'solde',
        'score_confiance',
        'statut_inscription',
        'documents',
        'disponible',
    ];

    protected $casts = [
        'specialites' => 'array',
        'forfaits' => 'array',
        'zone_intervention' => 'array',
        'documents' => 'array',
        'tarif_horaire' => 'decimal:2',
        'frais_deplacement' => 'decimal:2',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'solde' => 'decimal:2',
        'score_confiance' => 'decimal:2',
        'disponible' => 'boolean',
    ];

    /**
     * Relation avec User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relation avec Commandes
     */
    public function commandes(): HasMany
    {
        return $this->hasMany(Commande::class);
    }

    /**
     * Relation avec Avis
     */
    public function avis(): HasMany
    {
        return $this->hasMany(Avis::class);
    }

    /**
     * Relation avec Transactions
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Relation avec Retraits
     */
    public function retraits(): HasMany
    {
        return $this->hasMany(Retrait::class);
    }

    /**
     * Relation avec Contournements
     */
    public function contournements(): HasMany
    {
        return $this->hasMany(Contournement::class);
    }

    /**
     * Vérifier si le solde est négatif
     */
    public function hasNegativeBalance(): bool
    {
        return $this->solde < 0;
    }

    /**
     * Vérifier si le compte est validé
     */
    public function isValide(): bool
    {
        return $this->statut_inscription === 'valide';
    }

    /**
     * Vérifier si le compte est en attente
     */
    public function isPending(): bool
    {
        return $this->statut_inscription === 'en_attente';
    }
}
