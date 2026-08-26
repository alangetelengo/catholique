<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('caisse_mouvements', function (Blueprint $table): void {
            if (! Schema::hasColumn('caisse_mouvements', 'mois_capital')) {
                $table->string('mois_capital', 2)->nullable()->after('date_mouvement');
            }
            if (! Schema::hasColumn('caisse_mouvements', 'annee_capital')) {
                $table->unsignedSmallInteger('annee_capital')->nullable()->after('mois_capital');
            }

            $table->index(['caisse_id', 'mois_capital', 'annee_capital'], 'caisse_mouvements_envelope_idx');
        });

        Schema::table('expenses', function (Blueprint $table): void {
            if (! Schema::hasColumn('expenses', 'mois_capital')) {
                $table->string('mois_capital', 2)->nullable()->after('date_depense');
            }
            if (! Schema::hasColumn('expenses', 'annee_capital')) {
                $table->unsignedSmallInteger('annee_capital')->nullable()->after('mois_capital');
            }
        });

        Schema::table('expense_funding_sources', function (Blueprint $table): void {
            if (! Schema::hasColumn('expense_funding_sources', 'mois_capital')) {
                $table->string('mois_capital', 2)->nullable()->after('caisse_id');
            }
            if (! Schema::hasColumn('expense_funding_sources', 'annee_capital')) {
                $table->unsignedSmallInteger('annee_capital')->nullable()->after('mois_capital');
            }
        });

        if (Schema::hasTable('caisse_mouvements') && Schema::hasTable('revenues')) {
            DB::table('caisse_mouvements as cm')
                ->join('revenues as r', 'r.id', '=', 'cm.revenue_id')
                ->where('cm.type', 'credit_recette')
                ->whereNull('cm.mois_capital')
                ->update([
                    'cm.mois_capital' => DB::raw('COALESCE(r.mois_capital, LPAD(MONTH(r.date_recette), 2, "0"))'),
                    'cm.annee_capital' => DB::raw('YEAR(r.date_recette)'),
                ]);
        }
    }

    public function down(): void
    {
        Schema::table('expense_funding_sources', function (Blueprint $table): void {
            if (Schema::hasColumn('expense_funding_sources', 'annee_capital')) {
                $table->dropColumn('annee_capital');
            }
            if (Schema::hasColumn('expense_funding_sources', 'mois_capital')) {
                $table->dropColumn('mois_capital');
            }
        });

        Schema::table('expenses', function (Blueprint $table): void {
            if (Schema::hasColumn('expenses', 'annee_capital')) {
                $table->dropColumn('annee_capital');
            }
            if (Schema::hasColumn('expenses', 'mois_capital')) {
                $table->dropColumn('mois_capital');
            }
        });

        Schema::table('caisse_mouvements', function (Blueprint $table): void {
            if (Schema::hasIndex('caisse_mouvements', 'caisse_mouvements_envelope_idx')) {
                $table->dropIndex('caisse_mouvements_envelope_idx');
            }
            if (Schema::hasColumn('caisse_mouvements', 'annee_capital')) {
                $table->dropColumn('annee_capital');
            }
            if (Schema::hasColumn('caisse_mouvements', 'mois_capital')) {
                $table->dropColumn('mois_capital');
            }
        });
    }
};
