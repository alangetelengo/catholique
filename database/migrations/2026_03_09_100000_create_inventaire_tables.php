<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Inventaire Magasin = produits alimentaires
     * Inventaire Patrimoine = biens de la paroisse
     */
    public function up(): void
    {
        Schema::create('inventaire_magasin', function (Blueprint $table) {
            $table->id();
            $table->foreignId('paroisse_id')->nullable()->constrained('paroisses')->onDelete('cascade');
            $table->string('nom');
            $table->string('categorie')->nullable();
            $table->string('unite', 50)->default('unité');
            $table->decimal('quantite', 12, 2)->default(0);
            $table->decimal('quantite_min_alerte', 12, 2)->nullable();
            $table->date('date_peremption')->nullable();
            $table->string('emplacement')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('inventaire_patrimoine', function (Blueprint $table) {
            $table->id();
            $table->foreignId('paroisse_id')->nullable()->constrained('paroisses')->onDelete('cascade');
            $table->string('nom');
            $table->string('categorie')->nullable();
            $table->string('reference')->nullable();
            $table->text('description')->nullable();
            $table->string('lieu')->nullable();
            $table->decimal('valeur_estimee', 14, 2)->nullable();
            $table->date('date_acquisition')->nullable();
            $table->string('etat')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventaire_magasin');
        Schema::dropIfExists('inventaire_patrimoine');
    }
};
