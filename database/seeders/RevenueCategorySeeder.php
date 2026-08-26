<?php

namespace Database\Seeders;

use App\Models\Paroisse;
use App\Models\RevenueCategory;
use Illuminate\Database\Seeder;

class RevenueCategorySeeder extends Seeder
{
    /**
     * Catégories de recettes par défaut (une par paroisse).
     */
    public function run(): void
    {
        $categories = [
            [
                'code' => 'banque',
                'nom' => 'BANQUE (économat diocésain)',
                'description' => 'Versements de la hiérarchie — trésorerie générale uniquement',
                'ordre' => 0,
                'actif' => true,
            ],
            [
                'code' => 'quete_ordinaire',
                'nom' => 'Quête ordinaire',
                'description' => 'Messes de la semaine (lundi à samedi) et messe du dimanche',
                'ordre' => 1,
                'actif' => true,
            ],
            [
                'code' => 'quete_extraordinaire',
                'nom' => 'Quête extraordinaire',
                'description' => 'Mariage, obsèques, action de grâce, caritas, la grotte, etc.',
                'ordre' => 2,
                'actif' => true,
            ],
            [
                'code' => 'location',
                'nom' => 'Location',
                'description' => 'Loyers (boutiques), salle de fête, chapiteaux, cour de la paroisse',
                'ordre' => 3,
                'actif' => true,
            ],
            [
                'code' => 'subvention',
                'nom' => 'Subvention',
                'description' => 'Obsolète : remplacé par les caisses (crédit direct / virements)',
                'ordre' => 4,
                'actif' => false,
            ],
            [
                'code' => 'procure',
                'nom' => 'Procure',
                'description' => 'Dîmes, denier du culte, casuel (baptêmes des enfants)',
                'ordre' => 5,
                'actif' => true,
            ],
            [
                'code' => 'fete',
                'nom' => 'Fête',
                'description' => 'Fêtes de la paroisse',
                'ordre' => 6,
                'actif' => true,
            ],
        ];

        $paroisses = Paroisse::all();
        if ($paroisses->isEmpty()) {
            $this->command?->warn('Aucune paroisse trouvée. RevenueCategorySeeder ignoré.');

            return;
        }

        $count = 0;
        foreach ($paroisses as $paroisse) {
            foreach ($categories as $category) {
                RevenueCategory::updateOrCreate(
                    [
                        'paroisse_id' => $paroisse->id,
                        'code' => $category['code'],
                    ],
                    [
                        'nom' => $category['nom'],
                        'description' => $category['description'],
                        'actif' => $category['actif'] ?? true,
                        'ordre' => $category['ordre'],
                    ]
                );
                $count++;
            }
        }

        $this->command?->info("{$count} catégories de recettes créées ou mises à jour (toutes paroisses).");
    }
}
