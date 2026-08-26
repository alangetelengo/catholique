<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CaisseMouvement extends Model
{
    public const TYPE_CREDIT_DIRECT = 'credit_direct';

    public const TYPE_CREDIT_RECETTE = 'credit_recette';

    public const TYPE_VIREMENT = 'virement';

    public const TYPE_ALIMENTATION_RECETTE = 'alimentation_recette';

    public const TYPE_DEPENSE = 'depense';

    public const SENS_CREDIT = 'credit';

    public const SENS_DEBIT = 'debit';

    protected $fillable = [
        'paroisse_id',
        'caisse_id',
        'type',
        'sens',
        'montant',
        'date_mouvement',
        'mois_capital',
        'annee_capital',
        'libelle',
        'notes',
        'revenue_id',
        'revenue_type_id',
        'expense_id',
        'expense_funding_source_id',
        'contrepartie_caisse_id',
        'contrepartie_mouvement_id',
        'created_by',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'montant' => 'decimal:2',
            'date_mouvement' => 'date',
        ];
    }

    public function paroisse(): BelongsTo
    {
        return $this->belongsTo(Paroisse::class);
    }

    public function caisse(): BelongsTo
    {
        return $this->belongsTo(Caisse::class);
    }

    public function revenue(): BelongsTo
    {
        return $this->belongsTo(Revenue::class);
    }

    public function revenueType(): BelongsTo
    {
        return $this->belongsTo(RevenueType::class);
    }

    public function expense(): BelongsTo
    {
        return $this->belongsTo(Expense::class);
    }

    public function contrepartieCaisse(): BelongsTo
    {
        return $this->belongsTo(Caisse::class, 'contrepartie_caisse_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
