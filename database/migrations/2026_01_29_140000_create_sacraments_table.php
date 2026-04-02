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
        Schema::create('sacraments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('paroisse_id')->constrained('paroisses')->onDelete('cascade');
            $table->enum('type', ['bapteme', 'confirmation', 'communion', 'mariage', 'obseques']);
            $table->date('date_celebration');
            $table->string('lieu')->nullable();
            $table->foreignId('celebrant_id')->nullable()->constrained('members')->onDelete('set null');
            $table->string('beneficiary_name')->nullable();
            $table->foreignId('beneficiary_id')->nullable()->constrained('members')->onDelete('set null');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['paroisse_id', 'type', 'date_celebration']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sacraments');
    }
};
