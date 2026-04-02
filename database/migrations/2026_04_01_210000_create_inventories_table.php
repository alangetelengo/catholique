<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Inventaires par paroisse : biens mobiliers, matériel liturgique, etc.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('paroisse_id')->constrained()->cascadeOnDelete();
            $table->string('designation');
            $table->string('categorie', 64)->default('autre');
            $table->string('reference_inventaire', 128)->nullable();
            $table->decimal('quantite', 12, 2)->default(1);
            $table->string('unite', 64)->default('unité');
            $table->string('emplacement', 255)->nullable();
            $table->string('etat', 32)->default('bon');
            $table->date('date_acquisition')->nullable();
            $table->decimal('valeur_estimee', 14, 2)->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['paroisse_id', 'categorie']);
            $table->index(['paroisse_id', 'etat']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventories');
    }
};
