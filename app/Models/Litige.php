<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Litige extends Model
{
    protected $fillable = [
        'commande_id',
        'client_id',
        'prestataire_id',
        'type',
        'description',
        'preuves',
        'statut',
        'resolution',
        'decision',
        'montant_remboursement',
        'traite_par',
        'traite_le',
    ];

    protected $casts = [
        'preuves' => 'array',
        'traite_le' => 'datetime',
    ];

    public function commande(): BelongsTo
    {
        return $this->belongsTo(Commande::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function prestataire(): BelongsTo
    {
        return $this->belongsTo(Prestataire::class);
    }

    public function traitePar(): BelongsTo
    {
        return $this->belongsTo(User::class, 'traite_par');
    }
}
