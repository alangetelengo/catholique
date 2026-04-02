<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('revenue_categories', function (Blueprint $table): void {
            $table->id();
            $table->string('code')->unique();
            $table->string('nom');
            $table->text('description')->nullable();
            $table->boolean('actif')->default(true);
            $table->unsignedInteger('ordre')->default(0);
            $table->timestamps();
        });

        Schema::create('revenue_types', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('revenue_category_id')->constrained()->cascadeOnDelete();
            $table->string('code');
            $table->string('nom');
            $table->text('description')->nullable();
            $table->boolean('actif')->default(true);
            $table->unsignedInteger('ordre')->default(0);
            $table->timestamps();

            $table->unique(['revenue_category_id', 'code']);
        });

        Schema::create('revenues', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('revenue_category_id')->constrained()->restrictOnDelete();
            $table->foreignId('revenue_type_id')->constrained()->restrictOnDelete();
            $table->date('date_recette');
            $table->decimal('montant', 14, 2);
            $table->string('methode_paiement', 50);
            $table->string('reference_paiement')->nullable()->unique();
            $table->string('periode_messe', 30)->nullable();
            $table->string('jour_semaine', 20)->nullable();
            $table->string('mois_location', 7)->nullable();
            $table->text('notes')->nullable();
            $table->string('donateur_nom')->nullable();
            $table->string('donateur_telephone', 50)->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('revenues');
        Schema::dropIfExists('revenue_types');
        Schema::dropIfExists('revenue_categories');
    }
};
