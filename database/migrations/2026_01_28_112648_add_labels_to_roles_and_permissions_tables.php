<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $tableNames = config('permission.table_names');

        Schema::table($tableNames['roles'], function (Blueprint $table): void {
            $table->string('libelle_role')->nullable()->after('name');
        });

        Schema::table($tableNames['permissions'], function (Blueprint $table): void {
            $table->string('libelle_permission')->nullable()->after('name');
        });

        DB::table($tableNames['roles'])
            ->select(['id', 'name'])
            ->orderBy('id')
            ->get()
            ->each(function (object $role) use ($tableNames): void {
                DB::table($tableNames['roles'])
                    ->where('id', $role->id)
                    ->update([
                        'libelle_role' => ucfirst(str_replace('_', ' ', (string) $role->name)),
                    ]);
            });

        DB::table($tableNames['permissions'])
            ->select(['id', 'name'])
            ->orderBy('id')
            ->get()
            ->each(function (object $permission) use ($tableNames): void {
                DB::table($tableNames['permissions'])
                    ->where('id', $permission->id)
                    ->update([
                        'libelle_permission' => ucfirst(str_replace('_', ' ', (string) $permission->name)),
                    ]);
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tableNames = config('permission.table_names');

        Schema::table($tableNames['roles'], function (Blueprint $table): void {
            $table->dropColumn('libelle_role');
        });

        Schema::table($tableNames['permissions'], function (Blueprint $table): void {
            $table->dropColumn('libelle_permission');
        });
    }
};
