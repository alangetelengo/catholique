<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('revenues', 'mois_subvention')) {
            Schema::table('revenues', function (Blueprint $table): void {
                $table->string('mois_subvention', 7)->nullable()->after('mois_location');
                $table->index(['paroisse_id', 'revenue_type_id', 'mois_subvention'], 'revenues_popote_mois_idx');
            });
        }

        if (! Schema::hasColumn('expense_funding_sources', 'revenue_id')) {
            Schema::table('expense_funding_sources', function (Blueprint $table): void {
                $table->foreignId('revenue_id')->nullable()->after('revenue_type_id')->constrained('revenues')->nullOnDelete();
                $table->index(['revenue_id', 'expense_id'], 'efs_revenue_expense_idx');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('expense_funding_sources', 'revenue_id')) {
            Schema::table('expense_funding_sources', function (Blueprint $table): void {
                $table->dropForeign(['revenue_id']);
                $table->dropIndex('efs_revenue_expense_idx');
                $table->dropColumn('revenue_id');
            });
        }

        if (Schema::hasColumn('revenues', 'mois_subvention')) {
            Schema::table('revenues', function (Blueprint $table): void {
                $table->dropIndex('revenues_popote_mois_idx');
                $table->dropColumn('mois_subvention');
            });
        }
    }
};
