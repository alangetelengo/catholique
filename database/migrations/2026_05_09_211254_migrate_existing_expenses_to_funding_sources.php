<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Migrer toutes les dépenses existantes qui ont un revenue_type_id vers expense_funding_sources
        $expenses = DB::table('expenses')
            ->whereNotNull('revenue_type_id')
            ->whereNull('deleted_at')
            ->get();

        foreach ($expenses as $expense) {
            // Vérifier si cette dépense n'a pas déjà de sources de financement
            $existingSource = DB::table('expense_funding_sources')
                ->where('expense_id', $expense->id)
                ->exists();

            if (! $existingSource) {
                DB::table('expense_funding_sources')->insert([
                    'expense_id' => $expense->id,
                    'revenue_type_id' => $expense->revenue_type_id,
                    'montant_alloue' => $expense->montant,
                    'ordre' => 1,
                    'created_at' => $expense->created_at ?? now(),
                    'updated_at' => $expense->updated_at ?? now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        // Supprimer toutes les sources de financement qui ont ordre = 1 et montant_alloue = montant de la dépense
        // (ce qui indique qu'elles ont été créées par cette migration)
        DB::table('expense_funding_sources')
            ->where('ordre', 1)
            ->whereRaw('montant_alloue = (SELECT montant FROM expenses WHERE expenses.id = expense_funding_sources.expense_id)')
            ->delete();
    }
};
