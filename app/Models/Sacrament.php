<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Sacrament extends Model
{
    protected $fillable = [
        'paroisse_id',
        'type',
        'date_celebration',
        'lieu',
        'celebrant_id',
        'beneficiary_name',
        'beneficiary_id',
        'notes',
    ];

    protected $casts = [
        'date_celebration' => 'date',
    ];

    public const TYPES = [
        'bapteme' => 'Baptême',
        'confirmation' => 'Confirmation',
        'communion' => 'Communion',
        'mariage' => 'Mariage',
        'obseques' => 'Obsèques',
    ];

    public function paroisse(): BelongsTo
    {
        return $this->belongsTo(Paroisse::class);
    }

    public function celebrant(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'celebrant_id');
    }

    public function beneficiary(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'beneficiary_id');
    }

    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }
}
