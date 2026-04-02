<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventaireMagasin extends Model
{
    protected $table = 'inventaire_magasin';

    protected $fillable = [
        'paroisse_id', 'nom', 'categorie', 'unite', 'quantite',
        'quantite_min_alerte', 'date_peremption', 'emplacement', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'date_peremption' => 'date',
            'quantite' => 'decimal:2',
            'quantite_min_alerte' => 'decimal:2',
        ];
    }

    public function paroisse(): BelongsTo
    {
        return $this->belongsTo(Paroisse::class);
    }

    public function isAlerte(): bool
    {
        if ($this->quantite_min_alerte === null) {
            return false;
        }
        return $this->quantite <= $this->quantite_min_alerte;
    }
}
