<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Revenue extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'paroisse_id',
        'revenue_category_id',
        'revenue_type_id',
        'periode_messe',
        'jour_semaine',
        'mois_location',
        'mois_capital',
        'event_id',
        'montant',
        'date_recette',
        'est_recurrent',
        'frequence_recurrence',
        'methode_paiement',
        'reference_paiement',
        'statut',
        'notes',
        'donateur_nom',
        'donateur_telephone',
        'created_by',
        'validated_by',
        'validated_at',
    ];

    protected $casts = [
        'date_recette' => 'date',
        'est_recurrent' => 'bool',
        'validated_at' => 'datetime',
    ];

    public function paroisse(): BelongsTo
    {
        return $this->belongsTo(Paroisse::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(RevenueCategory::class, 'revenue_category_id');
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(RevenueType::class, 'revenue_type_id');
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class, 'event_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function validatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by');
    }
}
