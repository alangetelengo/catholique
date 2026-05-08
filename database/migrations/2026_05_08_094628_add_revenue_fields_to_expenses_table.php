<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('expenses', function (Blueprint $table): void {
            $table->foreignId('revenue_category_id')
                ->nullable()
                ->after('paroisse_id')
                ->constrained('revenue_categories')
                ->onDelete('restrict');

            $table->foreignId('revenue_type_id')
                ->nullable()
                ->after('revenue_category_id')
                ->constrained('revenue_types')
                ->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table): void {
            $table->dropForeign(['revenue_category_id']);
            $table->dropForeign(['revenue_type_id']);
            $table->dropColumn(['revenue_category_id', 'revenue_type_id']);
        });
    }
};
