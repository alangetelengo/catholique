<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expense_types', function (Blueprint $table): void {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('nom');
            $table->string('description')->nullable();
            $table->boolean('actif')->default(true);
            $table->unsignedInteger('ordre')->default(0);
            $table->timestamps();
        });

        $now = now();

        DB::table('expense_types')->insert([
            [
                'code' => 'alimentation_popote',
                'nom' => 'Alimentation / popote',
                'description' => 'Achats alimentaires et frais de popote',
                'actif' => true,
                'ordre' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'salaires',
                'nom' => 'Salaires',
                'description' => 'Rémunérations et charges salariales',
                'actif' => true,
                'ordre' => 2,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'carburant',
                'nom' => 'Carburant',
                'description' => 'Carburant et frais de déplacement liés',
                'actif' => true,
                'ordre' => 3,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'entretien_reparations',
                'nom' => 'Entretien / réparations',
                'description' => 'Entretien des locaux, matériel et réparations',
                'actif' => true,
                'ordre' => 4,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'factures',
                'nom' => 'Factures (eau, électricité, internet…)',
                'description' => 'Factures et abonnements (eau, électricité, internet, gaz…)',
                'actif' => true,
                'ordre' => 5,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'autre',
                'nom' => 'Autre',
                'description' => 'Toute autre dépense non classée',
                'actif' => true,
                'ordre' => 99,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('expense_types');
    }
};
