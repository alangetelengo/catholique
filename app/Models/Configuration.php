<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Configuration extends Model
{
    use HasFactory;

    protected $fillable = [
        'paroisse_id',
        'cle',
        'valeur',
        'type',
        'description',
        'actif',
    ];

    protected $casts = [
        'actif' => 'boolean',
    ];

    public function paroisse(): BelongsTo
    {
        return $this->belongsTo(Paroisse::class, 'paroisse_id');
    }

    public static function getByKey(?int $paroisseId, string $cle): ?self
    {
        return self::where('paroisse_id', $paroisseId)
            ->where('cle', $cle)
            ->where('actif', true)
            ->first();
    }

    public static function getValue(?int $paroisseId, string $cle, mixed $default = null): mixed
    {
        $config = self::getByKey($paroisseId, $cle);

        if (! $config) {
            return $default;
        }

        return match ($config->type) {
            'json' => json_decode($config->valeur, true),
            'integer' => (int) $config->valeur,
            'boolean' => filter_var($config->valeur, FILTER_VALIDATE_BOOLEAN),
            'float' => (float) $config->valeur,
            default => $config->valeur,
        };
    }

    public static function setValue(?int $paroisseId, string $cle, mixed $valeur, string $type = 'string', ?string $description = null): self
    {
        $config = self::where('paroisse_id', $paroisseId)
            ->where('cle', $cle)
            ->first();

        $formattedValue = match ($type) {
            'json' => json_encode($valeur),
            'boolean' => $valeur ? '1' : '0',
            default => (string) $valeur,
        };

        if ($config) {
            $config->update([
                'valeur' => $formattedValue,
                'type' => $type,
                'description' => $description ?? $config->description,
                'actif' => true,
            ]);
        } else {
            $config = self::create([
                'paroisse_id' => $paroisseId,
                'cle' => $cle,
                'valeur' => $formattedValue,
                'type' => $type,
                'description' => $description,
                'actif' => true,
            ]);
        }

        return $config;
    }
}
