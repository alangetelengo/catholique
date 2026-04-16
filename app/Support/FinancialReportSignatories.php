<?php

namespace App\Support;

/**
 * Blocs signataires par défaut pour les PDF / impressions de rapports financiers et de recettes.
 */
final class FinancialReportSignatories
{
    /**
     * @return list<array{titre: string, nom: string}>
     */
    public static function defaultPdfBlocks(): array
    {
        return [
            ['titre' => 'Le Curé', 'nom' => 'Nom et signature'],
            ['titre' => 'Le Gestionnaire', 'nom' => 'Nom et signature'],
            ['titre' => 'Le Vicaire Économe', 'nom' => 'Nom et signature'],
        ];
    }
}
