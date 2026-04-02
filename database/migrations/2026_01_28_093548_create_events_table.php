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
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('titre');
            $table->enum('type', ['messe', 'célébration', 'activité'])->default('activité');
            $table->date('date_evenement');
            $table->time('heure_evenement')->nullable();
            $table->string('lieu')->nullable();
            $table->foreignId('celebre_par_id')->nullable()->constrained('members')->onDelete('set null');
            $table->string('intention')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
