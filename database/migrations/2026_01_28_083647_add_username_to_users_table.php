<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('username', 80)
                ->nullable()
                ->unique()
                ->after('email');
        });

        DB::table('users')
            ->select(['id', 'email'])
            ->orderBy('id')
            ->get()
            ->each(function (object $user): void {
                $base = (string) Str::of((string) $user->email)
                    ->before('@')
                    ->lower()
                    ->replaceMatches('/[^a-z0-9_.-]+/', '')
                    ->trim('-_.');

                if ($base === '') {
                    $base = 'user';
                }

                DB::table('users')
                    ->where('id', $user->id)
                    ->update([
                        'username' => "{$base}-{$user->id}",
                    ]);
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique('users_username_unique');
            $table->dropColumn('username');
        });
    }
};
