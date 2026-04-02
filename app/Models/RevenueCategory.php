<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RevenueCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'paroisse_id',
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

    public function types(): HasMany
    {
        return $this->hasMany(RevenueType::class);
    }

    public function revenues(): HasMany
    {
        return $this->hasMany(Revenue::class);
    }
}

