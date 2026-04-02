<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Paroisse extends Model
{
    protected $fillable = [
        'nom',
        'adresse',
        'ville',
        'pays',
        'telephone',
        'email',
        'code_paroisse',
        'curé_id',
        'diocèse',
        'description',
        'actif',
    ];

    protected $casts = [
        'actif' => 'boolean',
    ];

    /**
     * Relation avec les configurations
     */
    public function configurations(): HasMany
    {
        return $this->hasMany(Configuration::class);
    }

    /**
     * Relation avec le curé (membre)
     */
    public function curé(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'curé_id');
    }

    /** Lignes d'inventaire (biens / matériel) rattachées à cette paroisse. */
    public function inventories(): HasMany
    {
        return $this->hasMany(Inventory::class);
    }
}
