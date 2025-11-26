<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Commande extends Model
{
    protected $fillable = [
        'client_id',
        'prestataire_id',
        'categorie_service_id',
        'sous_categorie_service_id',
        'statut',
        'type_commande',
        'description',
        'photos',
        'adresse_intervention',
        'latitude',
        'longitude',
        'date_heure_souhaitee',
        'montant_total',
        'montant_commission',
        'methode_paiement',
        'statut_paiement',
        'historique_statuts',
    ];

    protected $casts = [
        'photos' => 'array',
        'historique_statuts' => 'array',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'montant_total' => 'decimal:2',
        'montant_commission' => 'decimal:2',
        'date_heure_souhaitee' => 'datetime',
    ];

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

    /**
     * Relation avec CategorieService
     */
    public function categorieService(): BelongsTo
    {
        return $this->belongsTo(CategorieService::class);
    }

    /**
     * Relation avec SousCategorieService
     */
    public function sousCategorieService(): BelongsTo
    {
        return $this->belongsTo(SousCategorieService::class);
    }

    /**
     * Relation avec Transaction
     */
    public function transaction(): HasOne
    {
        return $this->hasOne(Transaction::class);
    }

    /**
     * Relation avec Avis
     */
    public function avis(): HasOne
    {
        return $this->hasOne(Avis::class);
    }
}
