<?php

namespace App\Support;

use App\Models\RevenueCategory;
use App\Models\RevenueType;
use Carbon\Carbon;

class SubventionMensuelle
{
    public const CATEGORY_CODE = 'subvention';

    public const POPOTE_TYPE_CODE = 'subvention_popote';

    /**
     * @return array<int, string>
     */
    public static function moisLabels(): array
    {
        return [
            1 => 'Janvier',
            2 => 'Février',
            3 => 'Mars',
            4 => 'Avril',
            5 => 'Mai',
            6 => 'Juin',
            7 => 'Juillet',
            8 => 'Août',
            9 => 'Septembre',
            10 => 'Octobre',
            11 => 'Novembre',
            12 => 'Décembre',
        ];
    }

    public static function requiresMoisSubvention(?RevenueCategory $category): bool
    {
        return $category !== null && $category->code === self::CATEGORY_CODE;
    }

    public static function isSubventionType(RevenueType $type): bool
    {
        $type->loadMissing('category');

        return $type->category?->code === self::CATEGORY_CODE;
    }

    public static function formatMoisLabel(?string $moisSubvention): string
    {
        if ($moisSubvention === null || ! preg_match('/^(\d{4})-(\d{2})$/', $moisSubvention, $matches)) {
            return '—';
        }

        $month = (int) $matches[2];
        $labels = self::moisLabels();

        return ($labels[$month] ?? $matches[2]).' '.$matches[1];
    }

    public static function isValidMoisSubvention(string $value): bool
    {
        if (! preg_match('/^(\d{4})-(\d{2})$/', $value, $matches)) {
            return false;
        }

        $month = (int) $matches[2];

        return $month >= 1 && $month <= 12;
    }

    public static function moisSubventionFromParts(int $year, int $month): string
    {
        return Carbon::create($year, $month, 1)->format('Y-m');
    }

    public static function envelopeLabel(RevenueType $type, ?string $moisSubvention): string
    {
        return $type->nom.' — '.self::formatMoisLabel($moisSubvention);
    }
}
