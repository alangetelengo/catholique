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
}

