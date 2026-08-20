<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('expenses', function (Blueprint $table): void {
            $table->foreignId('expense_type_id')
                ->nullable()
                ->after('revenue_category_id')
                ->constrained('expense_types')
                ->nullOnDelete();
        });

        $autreId = DB::table('expense_types')->where('code', 'autre')->value('id');

        if ($autreId) {
            DB::table('expenses')->whereNull('expense_type_id')->update([
                'expense_type_id' => $autreId,
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('expense_type_id');
        });
    }
};
