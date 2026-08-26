<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        $types = [
            [
                'code' => 'transport',
                'nom' => 'Transport / déplacements',
                'description' => 'Déplacements, carburant et frais de transport',
                'ordre' => 3,
            ],
            [
                'code' => 'liturgie',
                'nom' => 'Liturgie / culte',
                'description' => 'Hosties, bougies, encens et matériel liturgique',
                'ordre' => 6,
            ],
            [
                'code' => 'intendance',
                'nom' => 'Intendance / fournitures',
                'description' => 'Hygiène, fournitures et entretien courant',
                'ordre' => 7,
            ],
            [
                'code' => 'accueil_pastorale',
                'nom' => 'Accueil / pastorale',
                'description' => 'Réceptions, honoraires messes et conférences',
                'ordre' => 8,
            ],
        ];

        foreach ($types as $type) {
            $exists = DB::table('expense_types')->where('code', $type['code'])->exists();
            if ($exists) {
                DB::table('expense_types')->where('code', $type['code'])->update([
                    'nom' => $type['nom'],
                    'description' => $type['description'],
                    'actif' => true,
                    'ordre' => $type['ordre'],
                    'updated_at' => $now,
                ]);

                continue;
            }

            DB::table('expense_types')->insert([
                'code' => $type['code'],
                'nom' => $type['nom'],
                'description' => $type['description'],
                'actif' => true,
                'ordre' => $type['ordre'],
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        DB::table('expense_types')->where('code', 'carburant')->update([
            'nom' => 'Carburant (legacy)',
            'actif' => false,
            'updated_at' => $now,
        ]);

        DB::table('expense_types')->where('code', 'factures')->update([
            'nom' => 'Charges / factures',
            'description' => 'Factures et abonnements (eau, électricité, internet, gaz…)',
            'ordre' => 5,
            'updated_at' => $now,
        ]);

        DB::table('expense_types')->where('code', 'entretien_reparations')->update([
            'nom' => 'Entretien / réparations',
            'description' => 'Entretien des locaux, matériel et réparations',
            'ordre' => 4,
            'updated_at' => $now,
        ]);

        DB::table('expense_types')->where('code', 'autre')->update([
            'ordre' => 99,
            'updated_at' => $now,
        ]);
    }

    public function down(): void
    {
        DB::table('expense_types')->whereIn('code', [
            'transport',
            'liturgie',
            'intendance',
            'accueil_pastorale',
        ])->delete();

        DB::table('expense_types')->where('code', 'carburant')->update([
            'nom' => 'Carburant',
            'actif' => true,
            'updated_at' => now(),
        ]);
    }
};
