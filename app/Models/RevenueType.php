<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RevenueType extends Model
{
    use HasFactory;

    protected $fillable = [
        'paroisse_id',
        'revenue_category_id',
        'code',
        'nom',
        'description',
        'actif',
        'ordre',
    ];

    public function paroisse(): BelongsTo
    {
        return $this->belongsTo(Paroisse::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(RevenueCategory::class, 'revenue_category_id');
    }

    public function revenues(): HasMany
    {
        return $this->hasMany(Revenue::class);
    }
}

