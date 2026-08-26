<?php

namespace App\Support;

use App\Models\RevenueCategory;
use App\Models\RevenueType;

class SubventionMensuelle
{
    public const CATEGORY_CODE = 'subvention';

    public const POPOTE_TYPE_CODE = 'subvention_popote';

    /**
     * @return array<string, string>
     */
    public static function moisOptions(): array
    {
        return [
            '01' => 'Janvier',
            '02' => 'Février',
            '03' => 'Mars',
            '04' => 'Avril',
            '05' => 'Mai',
            '06' => 'Juin',
            '07' => 'Juillet',
            '08' => 'Août',
            '09' => 'Septembre',
            '10' => 'Octobre',
            '11' => 'Novembre',
            '12' => 'Décembre',
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function moisLabels(): array
    {
        $options = self::moisOptions();
        $labels = [];
        foreach ($options as $mm => $label) {
            $labels[(int) $mm] = $label;
        }

        return $labels;
    }

    public static function isSubventionType(RevenueType $type): bool
    {
        $type->loadMissing('category');

        return $type->category?->code === self::CATEGORY_CODE;
    }

    public static function isBanqueCategory(?RevenueCategory $category): bool
    {
        return $category !== null && $category->code === 'banque';
    }

    public static function formatMoisCapital(?string $moisCapital): string
    {
        if ($moisCapital === null || $moisCapital === '') {
            return '—';
        }

        $key = str_pad($moisCapital, 2, '0', STR_PAD_LEFT);

        return self::moisOptions()[$key] ?? $moisCapital;
    }

    /**
     * Libellé pour une clé AAAA-MM (rapports caisses / historiques).
     */
    public static function formatMoisLabel(?string $yearMonth): string
    {
        if ($yearMonth === null || ! preg_match('/^(\d{4})-(\d{2})$/', $yearMonth, $matches)) {
            return self::formatMoisCapital($yearMonth);
        }

        $labels = self::moisLabels();

        return ($labels[(int) $matches[2]] ?? $matches[2]).' '.$matches[1];
    }

    public static function isValidMoisCapital(string $value): bool
    {
        return array_key_exists($value, self::moisOptions());
    }
}
