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
        if (Schema::hasTable('group_member')) {
            return;
        }

        Schema::create('group_member', function (Blueprint $table) {
            $table->id();
            $table->foreignId('groupe_id')->constrained('groups')->onDelete('cascade');
            $table->foreignId('membre_id')->constrained('members')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['groupe_id', 'membre_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('group_member');
    }
};
