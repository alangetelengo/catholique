<?php

namespace App\Support;

/**
 * Règles métier : types de charge (`type_charge`) autorisés par catégorie (`categorie_charge`).
 * Source : config/expenses.php (`types_by_categorie`).
 */
final class ExpenseChargeCatalog
{
    /**
     * @return list<string>
     */
    public static function typesForCategory(string $categorieCharge): array
    {
        $map = config('expenses.types_by_categorie', []);
        if (! is_array($map) || ! isset($map[$categorieCharge]) || ! is_array($map[$categorieCharge])) {
            return [];
        }

        $allowedGlobal = config('expenses.type_charge_codes', []);
        $allowedGlobal = is_array($allowedGlobal) ? $allowedGlobal : [];

        $list = array_values(array_filter($map[$categorieCharge], 'is_string'));

        return array_values(array_intersect($list, $allowedGlobal));
    }

    public static function typeAllowedForCategory(string $categorieCharge, string $typeCharge): bool
    {
        return in_array($typeCharge, self::typesForCategory($categorieCharge), true);
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
        $map = config('expenses.types_by_categorie', []);
        if (! is_array($map)) {
            return [];
        }

        $result = [];
        foreach (array_keys($map) as $cat) {
            if (! is_string($cat)) {
                continue;
            }
            $labeled = self::labeledOptionsForCategory($cat);
            $rows = [];
            foreach (self::typesForCategory($cat) as $code) {
                $rows[] = [
                    'code' => $code,
                    'nom' => $labeled[$code] ?? $code,
                ];
            }
            $result[$cat] = $rows;
        }

        return $result;
    }

    /**
     * Premier type autorisé pour une catégorie (ex. défaut formulaire).
     */
    public static function defaultTypeForCategory(string $categorieCharge): string
    {
        $types = self::typesForCategory($categorieCharge);

        return $types[0] ?? 'autre';
    }
}
