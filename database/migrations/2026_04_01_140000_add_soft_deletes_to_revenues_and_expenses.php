<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('revenues') && ! Schema::hasColumn('revenues', 'deleted_at')) {
            Schema::table('revenues', function (Blueprint $table): void {
                $table->softDeletes();
            });
        }

        if (Schema::hasTable('expenses') && ! Schema::hasColumn('expenses', 'deleted_at')) {
            Schema::table('expenses', function (Blueprint $table): void {
                $table->softDeletes();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('revenues') && Schema::hasColumn('revenues', 'deleted_at')) {
            Schema::table('revenues', function (Blueprint $table): void {
                $table->dropSoftDeletes();
            });
        }

        if (Schema::hasTable('expenses') && Schema::hasColumn('expenses', 'deleted_at')) {
            Schema::table('expenses', function (Blueprint $table): void {
                $table->dropSoftDeletes();
            });
        }
    }
};

