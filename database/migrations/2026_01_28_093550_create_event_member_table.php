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
        if (Schema::hasTable('event_member')) {
            return;
        }

        Schema::create('event_member', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evenement_id')->constrained('events')->onDelete('cascade');
            $table->foreignId('membre_id')->constrained('members')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['evenement_id', 'membre_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_member');
    }
};
