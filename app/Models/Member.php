<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Member extends Model
{
    use HasFactory;

    protected $fillable = [
        'prenom',
        'nom',
        'date_naissance',
        'sexe',
        'adresse',
        'telephone',
        'email',
        'statut',
        'notes',
        'paroisse_id',
    ];

    protected $casts = [
        'date_naissance' => 'date',
    ];

    /**
     * Relation avec la paroisse
     */
    public function paroisse(): BelongsTo
    {
        return $this->belongsTo(Paroisse::class);
    }

    /**
     * Relation avec les groupes
     */
    public function groupes(): BelongsToMany
    {
        return $this->belongsToMany(Group::class, 'group_member', 'membre_id', 'groupe_id');
    }

    /**
     * Relation avec les événements
     */
    public function evenements(): BelongsToMany
    {
        return $this->belongsToMany(Event::class, 'event_member', 'membre_id', 'evenement_id');
    }

    /**
     * Relation avec les baptêmes
     */
    public function baptemes(): HasMany
    {
        return $this->hasMany(Baptism::class, 'membre_id');
    }

    /**
     * Accessor pour le nom complet
     */
    public function getNomCompletAttribute(): string
    {
        return "{$this->prenom} {$this->nom}";
    }
}
