<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CategorieService extends Model
{
    protected $fillable = [
        'nom',
        'description',
        'icone',
        'couleur',
        'prix_fixe',
        'commission_rate',
        'active',
    ];

    protected $casts = [
        'prix_fixe' => 'boolean',
        'commission_rate' => 'decimal:2',
        'active' => 'boolean',
    ];

    /**
     * Relation avec SousCategorieService
     */
    public function sousCategories(): HasMany
    {
        return $this->hasMany(SousCategorieService::class);
    }

    /**
     * Relation avec Commandes
     */
    public function commandes(): HasMany
    {
        return $this->hasMany(Commande::class);
    }
}
