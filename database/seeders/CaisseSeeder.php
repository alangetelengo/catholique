<?php

namespace Database\Seeders;

use App\Models\Caisse;
use App\Models\Paroisse;
use Illuminate\Database\Seeder;

class CaisseSeeder extends Seeder
{
    /**
     * @return list<array{code: string, nom: string, description: string, est_tresorerie: bool, ordre: int}>
     */
    public static function definitions(): array
    {
        return [
            [
                'code' => Caisse::CODE_TRESORERIE,
                'nom' => 'Trésorerie générale',
                'description' => 'Alimentée uniquement par les recettes Banque — ne finance pas directement les dépenses',
                'est_tresorerie' => true,
                'ordre' => 0,
            ],
            [
                'code' => 'transport',
                'nom' => 'Caisse transport',
                'description' => 'Déplacements, carburant et frais de transport',
                'est_tresorerie' => false,
                'ordre' => 1,
            ],
            [
                'code' => 'entretien',
                'nom' => 'Caisse entretien / réparations',
                'description' => 'Travaux, matériel et réparations',
                'est_tresorerie' => false,
                'ordre' => 2,
            ],
            [
                'code' => 'liturgie',
                'nom' => 'Caisse liturgie',
                'description' => 'Hosties, bougies, encens et matériel de culte',
                'est_tresorerie' => false,
                'ordre' => 3,
            ],
            [
                'code' => 'charges',
                'nom' => 'Caisse charges',
                'description' => 'Eau, gaz, internet, électricité et factures',
                'est_tresorerie' => false,
                'ordre' => 4,
            ],
            [
                'code' => 'intendance',
                'nom' => 'Caisse intendance',
                'description' => 'Hygiène, fournitures et blanchisserie',
                'est_tresorerie' => false,
                'ordre' => 5,
            ],
            [
                'code' => 'accueil_pastorale',
                'nom' => 'Caisse accueil / pastorale',
                'description' => 'Réceptions, apéritifs et honoraires pastoraux',
                'est_tresorerie' => false,
                'ordre' => 6,
            ],
            [
                'code' => 'alimentation_popote',
                'nom' => 'Caisse alimentation / popote',
                'description' => 'Achats alimentaires et frais de popote',
                'est_tresorerie' => false,
                'ordre' => 7,
            ],
            [
                'code' => 'salaires',
                'nom' => 'Caisse salaires',
                'description' => 'Rémunérations et gardiennage',
                'est_tresorerie' => false,
                'ordre' => 8,
            ],
            [
                'code' => 'divers',
                'nom' => 'Caisse divers',
                'description' => 'Dépenses non classées ailleurs',
                'est_tresorerie' => false,
                'ordre' => 99,
            ],
        ];
    }

    public function run(): void
    {
        $paroisses = Paroisse::query()->get();
        if ($paroisses->isEmpty()) {
            $this->command?->warn('Aucune paroisse trouvée. CaisseSeeder ignoré.');

            return;
        }

        $count = 0;
        foreach ($paroisses as $paroisse) {
            foreach (self::definitions() as $definition) {
                Caisse::query()->updateOrCreate(
                    [
                        'paroisse_id' => $paroisse->id,
                        'code' => $definition['code'],
                    ],
                    [
                        'nom' => $definition['nom'],
                        'description' => $definition['description'],
                        'est_tresorerie' => $definition['est_tresorerie'],
                        'actif' => true,
                        'ordre' => $definition['ordre'],
                    ]
                );
                $count++;
            }
        }

        $this->command?->info("{$count} caisses créées ou mises à jour.");
    }
}
