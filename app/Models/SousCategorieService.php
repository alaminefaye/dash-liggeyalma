<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SousCategorieService extends Model
{
    protected $fillable = [
        'categorie_service_id',
        'nom',
        'description',
        'prix_fixe',
        'active',
    ];

    protected $casts = [
        'prix_fixe' => 'decimal:2',
        'active' => 'boolean',
    ];

    /**
     * Relation avec CategorieService
     */
    public function categorieService(): BelongsTo
    {
        return $this->belongsTo(CategorieService::class);
    }

    /**
     * Relation avec Commandes
     */
    public function commandes(): HasMany
    {
        return $this->hasMany(Commande::class);
    }
}
