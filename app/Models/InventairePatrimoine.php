<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventairePatrimoine extends Model
{
    protected $table = 'inventaire_patrimoine';

    protected $fillable = [
        'paroisse_id', 'nom', 'categorie', 'reference', 'description',
        'lieu', 'valeur_estimee', 'date_acquisition', 'etat', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'date_acquisition' => 'date',
            'valeur_estimee' => 'decimal:2',
        ];
    }

    public function paroisse(): BelongsTo
    {
        return $this->belongsTo(Paroisse::class);
    }
}
