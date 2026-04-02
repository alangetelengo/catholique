<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Baptism extends Model
{
    protected $fillable = [
        'membre_id',
        'date_bapteme',
        'lieu',
        'celebre_par_id',
        'parrain_id',
        'marraine_id',
        'notes',
    ];

    protected $casts = [
        'date_bapteme' => 'date',
    ];

    public function membre(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'membre_id');
    }

    public function celebrePar(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'celebre_par_id');
    }

    public function parrain(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'parrain_id');
    }

    public function marraine(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'marraine_id');
    }
}
