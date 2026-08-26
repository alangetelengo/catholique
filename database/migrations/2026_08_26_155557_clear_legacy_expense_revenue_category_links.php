<?php

use App\Models\Expense;
use App\Models\RevenueCategory;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $subventionCategoryIds = RevenueCategory::query()
            ->where('code', 'subvention')
            ->pluck('id');

        if ($subventionCategoryIds->isEmpty()) {
            return;
        }

        Expense::query()
            ->whereIn('revenue_category_id', $subventionCategoryIds)
            ->update([
                'revenue_category_id' => null,
                'revenue_type_id' => null,
            ]);
    }

    public function down(): void
    {
        // Irreversible cleanup of legacy reporting links.
    }
};
