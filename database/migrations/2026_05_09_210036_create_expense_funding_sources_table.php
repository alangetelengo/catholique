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
        Schema::create('expense_funding_sources', function (Blueprint $table) {
            $table->id();
            $table->foreignId('expense_id')->constrained('expenses')->onDelete('cascade');
            $table->foreignId('revenue_type_id')->constrained('revenue_types')->onDelete('restrict');
            $table->decimal('montant_alloue', 15, 2);
            $table->integer('ordre')->default(1);
            $table->timestamps();

            $table->index(['expense_id', 'revenue_type_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expense_funding_sources');
    }
};
