<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Ajoute paroisse_id à la table groups pour le filtrage par paroisse.
     */
    public function up(): void
    {
        Schema::table('groups', function (Blueprint $table): void {
            $table->foreignId('paroisse_id')
                ->nullable()
                ->after('id')
                ->constrained('paroisses')
                ->onDelete('cascade');
        });

        $firstParoisseId = DB::table('paroisses')->value('id');
        if ($firstParoisseId) {
            DB::table('groups')->update(['paroisse_id' => $firstParoisseId]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('groups', function (Blueprint $table): void {
            $table->dropForeign(['paroisse_id']);
        });
    }
};
