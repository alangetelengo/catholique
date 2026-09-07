<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class FinancialReport extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'paroisse_id',
        'periode_type',
        'date_debut',
        'date_fin',
        'total_recettes',
        'total_depenses',
        'solde',
        'details_recettes',
        'details_depenses',
        'created_by',
    ];

    protected $casts = [
        'date_debut' => 'date',
        'date_fin' => 'date',
        'total_recettes' => 'decimal:2',
        'total_depenses' => 'decimal:2',
        'solde' => 'decimal:2',
        'details_recettes' => 'array',
        'details_depenses' => 'array',
    ];

    public function paroisse(): BelongsTo
    {
        return $this->belongsTo(Paroisse::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Lien « modifier » depuis la liste globale des rapports enregistrés (selon le type de période).
     */
    public function editUrlForList(): ?string
    {
        $details = is_array($this->details_recettes) ? $this->details_recettes : [];

        return match ($this->periode_type) {
            'popote_subvention' => route('popote-reports.edit', $this),
            'depenses' => route('financial-reports.expenses', array_filter([
                'tab' => 'synthese',
                'paroisse_id' => $this->paroisse_id,
                'date_debut' => $this->date_debut?->format('Y-m-d'),
                'date_fin' => $this->date_fin?->format('Y-m-d'),
                'calculated' => 1,
                'caisse_id' => is_array($this->details_depenses) ? ($this->details_depenses['caisse_id'] ?? null) : null,
                'expense_type_id' => is_array($this->details_depenses) ? ($this->details_depenses['expense_type_id'] ?? null) : null,
            ], fn ($value) => $value !== null && $value !== '')),
            'revenues_by_category' => isset($details['report_target'])
                ? route('revenue-reports.edit', $this)
                : route('financial-reports.revenues-by-category'),
            'total' => isset($details['report_target'])
                ? route('revenue-reports.edit', $this)
                : null,
            default => null,
        };
    }
}
