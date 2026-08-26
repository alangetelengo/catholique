<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Caisse extends Model
{
    public const CODE_TRESORERIE = 'tresorerie_generale';

    protected $fillable = [
        'paroisse_id',
        'code',
        'nom',
        'description',
        'est_tresorerie',
        'actif',
        'ordre',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'est_tresorerie' => 'boolean',
            'actif' => 'boolean',
        ];
    }

    public function paroisse(): BelongsTo
    {
        return $this->belongsTo(Paroisse::class);
    }

    public function mouvements(): HasMany
    {
        return $this->hasMany(CaisseMouvement::class);
    }

    public function isTresorerie(): bool
    {
        return (bool) $this->est_tresorerie;
    }
}
