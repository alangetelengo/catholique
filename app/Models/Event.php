<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Event extends Model
{
    protected $fillable = [
        'titre',
        'type',
        'date_evenement',
        'heure_evenement',
        'lieu',
        'celebre_par_id',
        'intention',
        'description',
        'paroisse_id',
    ];

    protected $casts = [
        'date_evenement' => 'date',
        'heure_evenement' => 'datetime',
    ];

    public function paroisse(): BelongsTo
    {
        return $this->belongsTo(Paroisse::class);
    }

    public function celebrePar(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'celebre_par_id');
    }

    public function participants(): BelongsToMany
    {
        return $this->belongsToMany(Member::class, 'event_member', 'evenement_id', 'membre_id');
    }
}
