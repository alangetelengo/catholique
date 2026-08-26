<?php

namespace App\Support;

use Carbon\Carbon;

class CapitalMensuel
{
    /**
     * @return array{mois_capital: string, annee_capital: int}
     */
    public static function envelopeFromDate(string $date): array
    {
        $carbon = Carbon::parse($date);

        return [
            'mois_capital' => $carbon->format('m'),
            'annee_capital' => (int) $carbon->format('Y'),
        ];
    }

    public static function envelopeKey(string $moisCapital, int $anneeCapital): string
    {
        return sprintf('%04d-%s', $anneeCapital, str_pad($moisCapital, 2, '0', STR_PAD_LEFT));
    }

    public static function formatEnvelopeLabel(string $moisCapital, int $anneeCapital): string
    {
        return SubventionMensuelle::formatMoisCapital($moisCapital).' '.$anneeCapital;
    }

    public static function isValidEnvelope(?string $moisCapital, ?int $anneeCapital): bool
    {
        return $moisCapital !== null
            && $moisCapital !== ''
            && SubventionMensuelle::isValidMoisCapital($moisCapital)
            && $anneeCapital !== null
            && $anneeCapital >= 2000
            && $anneeCapital <= 2100;
    }
}
