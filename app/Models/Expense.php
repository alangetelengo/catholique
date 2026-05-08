<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class Expense extends Model
{
    use HasFactory, SoftDeletes;

    protected static function booted(): void
    {
        static::creating(function (Expense $expense): void {
            if (! empty($expense->paroisse_id)) {
                return;
            }

            $userParoisseId = Auth::user()?->paroisse_id;
            if (! empty($userParoisseId)) {
                $expense->paroisse_id = (int) $userParoisseId;

                return;
            }

            $fallbackParoisseId = Paroisse::query()->value('id');
            if (! empty($fallbackParoisseId)) {
                $expense->paroisse_id = (int) $fallbackParoisseId;
            }
        });
    }

    protected $fillable = [
        'paroisse_id',
        'revenue_category_id',
        'revenue_type_id',
        'montant',
        'date_depense',
        'jour_semaine',
        'libelle',
        'facture_reference',
        'piece_facture_path',
        'piece_recu_path',
        'fournisseur',
        'methode_paiement',
        'statut',
        'notes',
        'created_by',
        'validated_by',
        'validated_at',
    ];

    protected $casts = [
        'date_depense' => 'date',
        'validated_at' => 'datetime',
    ];

    public function paroisse(): BelongsTo
    {
        return $this->belongsTo(Paroisse::class);
    }

    public function revenueCategory(): BelongsTo
    {
        return $this->belongsTo(RevenueCategory::class, 'revenue_category_id');
    }

    public function revenueType(): BelongsTo
    {
        return $this->belongsTo(RevenueType::class, 'revenue_type_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function validatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by');
    }
}
