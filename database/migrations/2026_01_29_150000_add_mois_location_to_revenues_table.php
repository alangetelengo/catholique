<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Ajoute le champ mois_location pour enregistrer le mois de paiement
     * des loyers (location boutique mensuelle).
     */
    public function up(): void
    {
        if (! Schema::hasColumn('revenues', 'mois_location')) {
            Schema::table('revenues', function (Blueprint $table): void {
                // Format: YYYY-MM (ex: 2026-01 pour Janvier 2026)
                $table->string('mois_location', 7)->nullable()->after('jour_semaine');
            });
        }

        $db = Schema::getConnection()->getDatabaseName();
        $indexExists = DB::select(
            'SELECT COUNT(1) AS c FROM information_schema.statistics WHERE table_schema = ? AND table_name = ? AND index_name = ?',
            [$db, 'revenues', 'revenues_mois_location_revenue_type_id_index']
        );
        if ((int) ($indexExists[0]->c ?? 0) === 0) {
            Schema::table('revenues', function (Blueprint $table): void {
                $table->index(['mois_location', 'revenue_type_id']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('revenues', 'mois_location')) {
            Schema::table('revenues', function (Blueprint $table): void {
                $table->dropIndex(['mois_location', 'revenue_type_id']);
                $table->dropColumn('mois_location');
            });
        }
    }
};
