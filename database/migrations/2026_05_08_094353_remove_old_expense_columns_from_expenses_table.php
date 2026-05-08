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
            $table->dropColumn(['categorie_charge', 'type_charge']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table): void {
            $table->enum('categorie_charge', ['charge_fixe', 'charge_variable', 'alimentation_popote'])->nullable()->after('revenue_type_id');
            $table->string('type_charge', 100)->nullable()->after('categorie_charge');
        });
    }
};
