<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExpenseFundingSource extends Model
{
    protected $fillable = [
        'expense_id',
        'revenue_type_id',
        'revenue_id',
        'montant_alloue',
        'ordre',
    ];

    protected $casts = [
        'montant_alloue' => 'decimal:2',
        'ordre' => 'integer',
    ];

    public function expense(): BelongsTo
    {
        return $this->belongsTo(Expense::class);
    }

    public function revenueType(): BelongsTo
    {
        return $this->belongsTo(RevenueType::class);
    }

    public function revenue(): BelongsTo
    {
        return $this->belongsTo(Revenue::class);
    }
}
