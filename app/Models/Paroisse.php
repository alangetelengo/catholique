<?php

namespace App\Models;

use Database\Seeders\CaisseSeeder;
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
        'cure_id',
        'diocese',
        'description',
        'actif',
    ];

    protected $casts = [
        'actif' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::created(function (Paroisse $paroisse): void {
            if (! class_exists(CaisseSeeder::class)) {
                return;
            }

            foreach (CaisseSeeder::definitions() as $definition) {
                Caisse::query()->firstOrCreate(
                    [
                        'paroisse_id' => $paroisse->id,
                        'code' => $definition['code'],
                    ],
                    [
                        'nom' => $definition['nom'],
                        'description' => $definition['description'],
                        'est_tresorerie' => $definition['est_tresorerie'],
                        'actif' => true,
                        'ordre' => $definition['ordre'],
                    ]
                );
            }
        });
    }

    public function caisses(): HasMany
    {
        return $this->hasMany(Caisse::class);
    }

    public function configurations(): HasMany
    {
        return $this->hasMany(Configuration::class);
    }

    /**
     * Relation avec le curé (membre)
     */
    public function cure(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'cure_id');
    }

    /** Lignes d'inventaire (biens / matériel) rattachées à cette paroisse. */
    public function inventories(): HasMany
    {
        return $this->hasMany(Inventory::class);
    }
}
