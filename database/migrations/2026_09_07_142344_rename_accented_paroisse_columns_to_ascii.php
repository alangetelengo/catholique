<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Les colonnes accentuées (curé_id / diocèse) ont pu être créées corrompues
 * (ex. cur??_id) selon le charset client MySQL. On normalise en ASCII.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('paroisses')) {
            return;
        }

        $columns = Schema::getColumnListing('paroisses');

        $this->renameLegacyColumn(
            $columns,
            target: 'cure_id',
            matcher: static fn (string $column): bool => str_starts_with($column, 'cur')
                && str_ends_with($column, '_id')
                && $column !== 'cure_id',
            readdForeign: true,
        );

        $columns = Schema::getColumnListing('paroisses');

        $this->renameLegacyColumn(
            $columns,
            target: 'diocese',
            matcher: static fn (string $column): bool => str_starts_with($column, 'dioc')
                && str_ends_with($column, 'se')
                && $column !== 'diocese',
            readdForeign: false,
        );
    }

    public function down(): void
    {
        // Irreversible volontairement : les accents en noms de colonnes sont fragiles.
    }

    /**
     * @param  list<string>  $columns
     * @param  callable(string): bool  $matcher
     */
    private function renameLegacyColumn(array $columns, string $target, callable $matcher, bool $readdForeign): void
    {
        if (in_array($target, $columns, true)) {
            return;
        }

        $legacy = null;
        foreach ($columns as $column) {
            if ($matcher($column)) {
                $legacy = $column;
                break;
            }
        }

        if ($legacy === null) {
            return;
        }

        $this->dropForeignKeysOnColumn('paroisses', $legacy);

        Schema::table('paroisses', function (Blueprint $table) use ($legacy, $target): void {
            $table->renameColumn($legacy, $target);
        });

        if ($readdForeign && Schema::hasTable('members')) {
            Schema::table('paroisses', function (Blueprint $table) use ($target): void {
                $table->foreign($target)->references('id')->on('members')->onDelete('set null');
            });
        }
    }

    private function dropForeignKeysOnColumn(string $table, string $column): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'sqlite') {
            return;
        }

        $database = Schema::getConnection()->getDatabaseName();

        $constraints = DB::select(
            'SELECT CONSTRAINT_NAME AS name
             FROM information_schema.KEY_COLUMN_USAGE
             WHERE TABLE_SCHEMA = ?
               AND TABLE_NAME = ?
               AND COLUMN_NAME = ?
               AND REFERENCED_TABLE_NAME IS NOT NULL',
            [$database, $table, $column]
        );

        foreach ($constraints as $constraint) {
            $name = $constraint->name ?? null;
            if (! is_string($name) || $name === '') {
                continue;
            }

            DB::statement("ALTER TABLE `{$table}` DROP FOREIGN KEY `{$name}`");
        }
    }
};
