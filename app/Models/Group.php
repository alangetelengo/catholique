<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Group extends Model
{
    protected $fillable = [
        'nom',
        'type',
        'responsable_id',
        'description',
        'paroisse_id',
    ];

    public function responsable(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'responsable_id');
    }

    public function paroisse(): BelongsTo
    {
        return $this->belongsTo(Paroisse::class);
    }

    public function membres(): BelongsToMany
    {
        return $this->belongsToMany(Member::class, 'group_member', 'groupe_id', 'membre_id');
    }
}
