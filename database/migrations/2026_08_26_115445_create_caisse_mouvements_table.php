<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('caisse_mouvements', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('paroisse_id')->constrained('paroisses')->cascadeOnDelete();
            $table->foreignId('caisse_id')->constrained('caisses')->restrictOnDelete();
            $table->string('type', 40);
            $table->string('sens', 10);
            $table->decimal('montant', 15, 2);
            $table->date('date_mouvement');
            $table->string('libelle')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('revenue_id')->nullable()->constrained('revenues')->nullOnDelete();
            $table->foreignId('revenue_type_id')->nullable()->constrained('revenue_types')->nullOnDelete();
            $table->foreignId('expense_id')->nullable()->constrained('expenses')->nullOnDelete();
            $table->foreignId('expense_funding_source_id')->nullable()->constrained('expense_funding_sources')->nullOnDelete();
            $table->foreignId('contrepartie_caisse_id')->nullable()->constrained('caisses')->nullOnDelete();
            $table->unsignedBigInteger('contrepartie_mouvement_id')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['caisse_id', 'date_mouvement']);
            $table->index(['paroisse_id', 'type']);
            $table->index('contrepartie_mouvement_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('caisse_mouvements');
    }
};
