<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Chaque paroisse gère ses propres catégories et types de recettes.
     */
    public function up(): void
    {
        $firstParoisseId = DB::table('paroisses')->value('id');
        $hasIndex = function (string $table, string $indexName): bool {
            $db = DB::getDatabaseName();
            $rows = DB::select(
                'SELECT COUNT(1) AS c FROM information_schema.statistics WHERE table_schema = ? AND table_name = ? AND index_name = ?',
                [$db, $table, $indexName]
            );

            return (int) ($rows[0]->c ?? 0) > 0;
        };

        // revenue_categories
        if (! Schema::hasColumn('revenue_categories', 'paroisse_id')) {
            Schema::table('revenue_categories', function (Blueprint $table): void {
                $table->foreignId('paroisse_id')->nullable()->after('id')->constrained('paroisses')->onDelete('cascade');
            });
        }
        if ($firstParoisseId) {
            DB::table('revenue_categories')->update(['paroisse_id' => $firstParoisseId]);
            if (Schema::hasColumn('revenue_categories', 'paroisse_id')) {
                Schema::table('revenue_categories', function (Blueprint $table): void {
                    $table->foreignId('paroisse_id')->nullable(false)->change();
                });
            }
        }
        if ($hasIndex('revenue_categories', 'revenue_categories_code_unique')) {
            Schema::table('revenue_categories', function (Blueprint $table): void {
                $table->dropUnique('revenue_categories_code_unique');
            });
        }
        if (! $hasIndex('revenue_categories', 'revenue_categories_paroisse_id_code_unique')) {
            Schema::table('revenue_categories', function (Blueprint $table): void {
                $table->unique(['paroisse_id', 'code']);
            });
        }

        // revenue_types
        if (! Schema::hasColumn('revenue_types', 'paroisse_id')) {
            Schema::table('revenue_types', function (Blueprint $table): void {
                $table->foreignId('paroisse_id')->nullable()->after('revenue_category_id')->constrained('paroisses')->onDelete('cascade');
            });
        }
        if ($firstParoisseId) {
            $types = DB::table('revenue_types')->get();
            foreach ($types as $t) {
                $paroisseId = DB::table('revenue_categories')->where('id', $t->revenue_category_id)->value('paroisse_id') ?? $firstParoisseId;
                DB::table('revenue_types')->where('id', $t->id)->update(['paroisse_id' => $paroisseId]);
            }
            if (Schema::hasColumn('revenue_types', 'paroisse_id')) {
                Schema::table('revenue_types', function (Blueprint $table): void {
                    $table->foreignId('paroisse_id')->nullable(false)->change();
                });
            }
        }
        if ($hasIndex('revenue_types', 'revenue_types_code_unique')) {
            Schema::table('revenue_types', function (Blueprint $table): void {
                $table->dropUnique('revenue_types_code_unique');
            });
        }
        if (! $hasIndex('revenue_types', 'revenue_types_paroisse_id_code_unique')) {
            Schema::table('revenue_types', function (Blueprint $table): void {
                $table->unique(['paroisse_id', 'code']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('revenue_categories', function (Blueprint $table): void {
            $table->dropUnique(['paroisse_id', 'code']);
            $table->unique('code');
            $table->dropForeign(['paroisse_id']);
        });
        Schema::table('revenue_types', function (Blueprint $table): void {
            $table->dropUnique(['paroisse_id', 'code']);
            $table->unique('code');
            $table->dropForeign(['paroisse_id']);
        });
    }
};
