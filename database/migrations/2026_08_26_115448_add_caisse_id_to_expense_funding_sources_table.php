<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('expense_funding_sources', function (Blueprint $table): void {
            $table->foreignId('caisse_id')
                ->nullable()
                ->after('expense_id')
                ->constrained('caisses')
                ->restrictOnDelete();
        });

        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            $foreignKeys = DB::select("
                SELECT CONSTRAINT_NAME
                FROM information_schema.KEY_COLUMN_USAGE
                WHERE TABLE_SCHEMA = DATABASE()
                  AND TABLE_NAME = 'expense_funding_sources'
                  AND COLUMN_NAME = 'revenue_type_id'
                  AND REFERENCED_TABLE_NAME IS NOT NULL
            ");

            foreach ($foreignKeys as $foreignKey) {
                $name = $foreignKey->CONSTRAINT_NAME;
                DB::statement("ALTER TABLE expense_funding_sources DROP FOREIGN KEY `{$name}`");
            }

            DB::statement('ALTER TABLE expense_funding_sources MODIFY revenue_type_id BIGINT UNSIGNED NULL');
            DB::statement('ALTER TABLE expense_funding_sources ADD CONSTRAINT expense_funding_sources_revenue_type_id_foreign FOREIGN KEY (revenue_type_id) REFERENCES revenue_types(id) ON DELETE RESTRICT');
        } else {
            Schema::table('expense_funding_sources', function (Blueprint $table): void {
                $table->dropForeign(['revenue_type_id']);
            });

            Schema::table('expense_funding_sources', function (Blueprint $table): void {
                $table->unsignedBigInteger('revenue_type_id')->nullable()->change();
                $table->foreign('revenue_type_id')->references('id')->on('revenue_types')->restrictOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::table('expense_funding_sources', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('caisse_id');
        });
    }
};
