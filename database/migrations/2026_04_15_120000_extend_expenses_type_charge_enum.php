<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Étend l’ENUM `type_charge` (nouveaux postes de charges fixes / variables).
     */
    public function up(): void
    {
        if (! Schema::hasTable('expenses')) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();
        if ($driver !== 'mysql') {
            return;
        }

        DB::statement("ALTER TABLE expenses MODIFY COLUMN type_charge ENUM(
            'carburant','hosties','internet','maintenance_materiel','gaz','eau','electricite',
            'gardiennage','salaire_ouvrier','autre','alimentation',
            'poubelles_sacs','fournitures_bureau','deplacement_ouvrier','plomberie','conseil','blanchisserie','activites_pastorales'
        ) NOT NULL DEFAULT 'autre'");
    }

    /**
     * Revenir à l’ENUM précédent (perd les valeurs des nouveaux types s’ils sont utilisés).
     */
    public function down(): void
    {
        if (! Schema::hasTable('expenses')) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();
        if ($driver !== 'mysql') {
            return;
        }

        DB::statement("ALTER TABLE expenses MODIFY COLUMN type_charge ENUM(
            'carburant','hosties','internet','maintenance_materiel','gaz','eau','electricite',
            'gardiennage','salaire_ouvrier','autre','alimentation'
        ) NOT NULL DEFAULT 'autre'");
    }
};
