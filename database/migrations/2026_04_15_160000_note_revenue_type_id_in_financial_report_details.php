<?php

use Illuminate\Database\Migrations\Migration;

/**
 * Les rapports enregistrés (periode_type = revenues_by_category) stockent dans
 * `financial_reports.details_recettes` (JSON) les clés optionnelles :
 * - revenue_category_id
 * - revenue_type_id  (filtrage par type de recette, lié à une catégorie)
 *
 * Aucune modification de schéma SQL n’est requise : la colonne JSON existante suffit.
 */
return new class extends Migration
{
    public function up(): void
    {
        //
    }

    public function down(): void
    {
        //
    }
};
