<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        $expenseTypes = [
            'alimentation_popote' => [
                'nom' => 'Alimentation / popote',
                'description' => 'Achats alimentaires et frais de popote',
            ],
            'salaires' => [
                'nom' => 'Salaires',
                'description' => 'Rémunérations et charges salariales',
            ],
            'carburant' => [
                'nom' => 'Carburant (legacy)',
                'description' => 'Carburant et frais de déplacement liés',
            ],
            'transport' => [
                'nom' => 'Transport / déplacements',
                'description' => 'Déplacements, carburant et frais de transport',
            ],
            'entretien_reparations' => [
                'nom' => 'Entretien / réparations',
                'description' => 'Entretien des locaux, matériel et réparations',
            ],
            'factures' => [
                'nom' => 'Charges / factures',
                'description' => 'Factures et abonnements (eau, électricité, internet, gaz…)',
            ],
            'liturgie' => [
                'nom' => 'Liturgie / culte',
                'description' => 'Hosties, bougies, encens et matériel liturgique',
            ],
            'intendance' => [
                'nom' => 'Intendance / fournitures',
                'description' => 'Hygiène, fournitures et entretien courant',
            ],
            'accueil_pastorale' => [
                'nom' => 'Accueil / pastorale',
                'description' => 'Réceptions, honoraires messes et conférences',
            ],
            'autre' => [
                'nom' => 'Autre',
                'description' => 'Toute autre dépense non classée',
            ],
        ];

        foreach ($expenseTypes as $code => $payload) {
            DB::table('expense_types')->where('code', $code)->update([
                'nom' => $payload['nom'],
                'description' => $payload['description'],
                'updated_at' => $now,
            ]);
        }

        if (Schema::hasTable('caisses')) {
            $caisses = [
                'tresorerie_generale' => 'Trésorerie générale',
                'transport' => 'Caisse transport',
                'entretien' => 'Caisse entretien / réparations',
                'liturgie' => 'Caisse liturgie',
                'charges' => 'Caisse charges',
                'intendance' => 'Caisse intendance',
                'accueil_pastorale' => 'Caisse accueil / pastorale',
                'alimentation_popote' => 'Caisse alimentation / popote',
                'salaires' => 'Caisse salaires',
                'divers' => 'Caisse divers',
            ];

            foreach ($caisses as $code => $nom) {
                DB::table('caisses')->where('code', $code)->update([
                    'nom' => $nom,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    public function down(): void
    {
        //
    }
};
