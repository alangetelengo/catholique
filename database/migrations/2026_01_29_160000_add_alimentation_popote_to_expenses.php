<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * - Dépenses alimentation (subvention popote) : libellé de l'alimentation, jour.
     * - Catégorie alimentation_popote et type alimentation.
     */
    public function up(): void
    {
        Schema::table('expenses', function (Blueprint $table): void {
            $table->string('libelle', 500)->nullable()->after('notes');
            $table->string('jour_semaine', 20)->nullable()->after('date_depense');
        });

        $driver = Schema::getConnection()->getDriverName();
        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE expenses MODIFY COLUMN categorie_charge ENUM('charge_fixe','charge_variable','charge_exceptionnelle','alimentation_popote') DEFAULT 'charge_fixe'");
            DB::statement("ALTER TABLE expenses MODIFY COLUMN type_charge ENUM('carburant','hosties','internet','maintenance_materiel','gaz','eau','electricite','gardiennage','salaire_ouvrier','autre','alimentation')");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table): void {
            $table->dropColumn(['libelle', 'jour_semaine']);
        });

        $driver = Schema::getConnection()->getDriverName();
        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE expenses MODIFY COLUMN categorie_charge ENUM('charge_fixe','charge_variable','charge_exceptionnelle') DEFAULT 'charge_fixe'");
            DB::statement("ALTER TABLE expenses MODIFY COLUMN type_charge ENUM('carburant','hosties','internet','maintenance_materiel','gaz','eau','electricite','gardiennage','salaire_ouvrier','autre')");
        }
    }
};
