<?php

namespace App\Support;

/**
 * Catalogue des types de charge (`type_charge`) et helpers de validation.
 */
final class ExpenseChargeCatalog
{
    /**
     * @return list<string>
     */
    public static function allTypeCodes(): array
    {
        $codes = config('expenses.type_charge_codes', []);

        return array_values(array_filter(is_array($codes) ? $codes : [], 'is_string'));
    }

    /**
     * @return list<string>
     */
    public static function typesForCategory(string $categorieCharge): array
    {
        if ($categorieCharge === 'alimentation_popote') {
            return ['alimentation'];
        }

        return array_values(array_filter(
            self::allTypeCodes(),
            static fn (string $code): bool => $code !== 'alimentation'
        ));
    }

    public static function typeAllowedForCategory(string $categorieCharge, string $typeCharge): bool
    {
        if (! in_array($typeCharge, self::allTypeCodes(), true)) {
            return false;
        }

        if ($categorieCharge === 'alimentation_popote') {
            return $typeCharge === 'alimentation';
        }

        return $typeCharge !== 'alimentation';
    }

    /**
     * @return array<string, string> code => libellé (locale courante)
     */
    public static function labeledOptionsForCategory(string $categorieCharge): array
    {
        $labels = trans('expenses.types');
        $labels = is_array($labels) ? $labels : [];
        $out = [];
        foreach (self::typesForCategory($categorieCharge) as $code) {
            $out[$code] = $labels[$code] ?? $code;
        }

        return $out;
    }

    /**
     * Pour les formulaires / filtres : toutes les catégories sauf popote (gérée à part).
     *
     * @return array<string, array<string, string>>
     */
    public static function labeledOptionsByCategoryExcludingPopote(): array
    {
        $out = [];
        foreach (['charge_fixe', 'charge_variable', 'charge_exceptionnelle'] as $cat) {
            $out[$cat] = self::labeledOptionsForCategory($cat);
        }

        return $out;
    }

    /**
     * Options pour le rapport « dépenses par catégorie » (JSON).
     *
     * @return array<string, list<array{code: string, nom: string}>>
     */
    public static function typeOptionRowsByCategory(): array
    {
        $rows = self::typeOptionRows();

        return [
            'charge_fixe' => $rows,
            'charge_variable' => $rows,
            'charge_exceptionnelle' => $rows,
            'alimentation_popote' => $rows,
        ];
    }

    /**
     * @return list<array{code: string, nom: string}>
     */
    public static function typeOptionRows(): array
    {
        $labels = trans('expenses.types');
        $labels = is_array($labels) ? $labels : [];
        $rows = [];
        foreach (self::allTypeCodes() as $code) {
            $rows[] = [
                'code' => $code,
                'nom' => $labels[$code] ?? $code,
            ];
        }

        return $rows;
    }

    /**
     * Premier type autorisé pour une catégorie (ex. défaut formulaire).
     */
    public static function defaultTypeForCategory(string $categorieCharge): string
    {
        if ($categorieCharge === 'alimentation_popote') {
            return 'alimentation';
        }

        return 'autre';
    }
}
