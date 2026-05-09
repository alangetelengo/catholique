<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('financial_reports') && ! Schema::hasColumn('financial_reports', 'deleted_at')) {
            Schema::table('financial_reports', function (Blueprint $table): void {
                $table->softDeletes();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('financial_reports') && Schema::hasColumn('financial_reports', 'deleted_at')) {
            Schema::table('financial_reports', function (Blueprint $table): void {
                $table->dropSoftDeletes();
            });
        }
    }
};
