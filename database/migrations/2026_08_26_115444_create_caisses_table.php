<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('caisses', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('paroisse_id')->constrained('paroisses')->cascadeOnDelete();
            $table->string('code', 50);
            $table->string('nom');
            $table->string('description')->nullable();
            $table->boolean('est_tresorerie')->default(false);
            $table->boolean('actif')->default(true);
            $table->unsignedInteger('ordre')->default(0);
            $table->timestamps();

            $table->unique(['paroisse_id', 'code']);
            $table->index(['paroisse_id', 'actif']);
        });

        $now = now();
        $definitions = [
            ['code' => 'tresorerie_generale', 'nom' => 'Trésorerie générale', 'description' => 'Alimentée uniquement par les recettes Banque', 'est_tresorerie' => true, 'ordre' => 0],
            ['code' => 'transport', 'nom' => 'Caisse transport', 'description' => 'Déplacements, carburant et frais de transport', 'est_tresorerie' => false, 'ordre' => 1],
            ['code' => 'entretien', 'nom' => 'Caisse entretien / réparations', 'description' => 'Travaux, matériel et réparations', 'est_tresorerie' => false, 'ordre' => 2],
            ['code' => 'liturgie', 'nom' => 'Caisse liturgie', 'description' => 'Hosties, bougies, encens et matériel de culte', 'est_tresorerie' => false, 'ordre' => 3],
            ['code' => 'charges', 'nom' => 'Caisse charges', 'description' => 'Eau, gaz, internet, électricité et factures', 'est_tresorerie' => false, 'ordre' => 4],
            ['code' => 'intendance', 'nom' => 'Caisse intendance', 'description' => 'Hygiène, fournitures et blanchisserie', 'est_tresorerie' => false, 'ordre' => 5],
            ['code' => 'accueil_pastorale', 'nom' => 'Caisse accueil / pastorale', 'description' => 'Réceptions, apéritifs et honoraires pastoraux', 'est_tresorerie' => false, 'ordre' => 6],
            ['code' => 'alimentation_popote', 'nom' => 'Caisse alimentation / popote', 'description' => 'Achats alimentaires et frais de popote', 'est_tresorerie' => false, 'ordre' => 7],
            ['code' => 'salaires', 'nom' => 'Caisse salaires', 'description' => 'Rémunérations et gardiennage', 'est_tresorerie' => false, 'ordre' => 8],
            ['code' => 'divers', 'nom' => 'Caisse divers', 'description' => 'Dépenses non classées ailleurs', 'est_tresorerie' => false, 'ordre' => 99],
        ];

        foreach (DB::table('paroisses')->pluck('id') as $paroisseId) {
            foreach ($definitions as $definition) {
                DB::table('caisses')->insert([
                    'paroisse_id' => $paroisseId,
                    'code' => $definition['code'],
                    'nom' => $definition['nom'],
                    'description' => $definition['description'],
                    'est_tresorerie' => $definition['est_tresorerie'],
                    'actif' => true,
                    'ordre' => $definition['ordre'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('caisses');
    }
};
