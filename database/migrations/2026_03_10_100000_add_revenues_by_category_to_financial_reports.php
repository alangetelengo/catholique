<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE financial_reports MODIFY COLUMN periode_type ENUM('semaine', 'dimanche', 'total', 'revenues_by_category') NOT NULL DEFAULT 'total'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE financial_reports MODIFY COLUMN periode_type ENUM('semaine', 'dimanche', 'total') NOT NULL DEFAULT 'total'");
    }
};
