<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

/**
 * Ligne d'inventaire rattachée à une paroisse (biens, matériel, consommables).
 * Le paroisse_id est renseigné automatiquement à la création si absent (comme Expense).
 */
class Inventory extends Model
{
    use HasFactory, SoftDeletes;

    protected static function booted(): void
    {
        static::creating(function (Inventory $row): void {
            if (! empty($row->paroisse_id)) {
                return;
            }

            $userParoisseId = Auth::user()?->paroisse_id;
            if (! empty($userParoisseId)) {
                $row->paroisse_id = (int) $userParoisseId;

                return;
            }

            $fallback = Paroisse::query()->value('id');
            if (! empty($fallback)) {
                $row->paroisse_id = (int) $fallback;
            }
        });
    }

    protected $fillable = [
        'paroisse_id',
        'designation',
        'categorie',
        'reference_inventaire',
        'quantite',
        'unite',
        'emplacement',
        'etat',
        'date_acquisition',
        'valeur_estimee',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'date_acquisition' => 'date',
        'quantite' => 'decimal:2',
        'valeur_estimee' => 'decimal:2',
    ];

    public function paroisse(): BelongsTo
    {
        return $this->belongsTo(Paroisse::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
