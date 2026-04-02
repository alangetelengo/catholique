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
                'code' => 'quete_ordinaire',
                'nom' => 'Quête Ordinaire',
                'description' => 'Messes de la semaine (Lundi à Samedi) et messe du dimanche',
                'ordre' => 1,
            ],
            [
                'code' => 'quete_extraordinaire',
                'nom' => 'Quête Extraordinaire',
                'description' => 'Mariage, obsèques, action de grâce, caritas, la grotte, etc.',
                'ordre' => 2,
            ],
            [
                'code' => 'location',
                'nom' => 'Location',
                'description' => 'Loyers (boutiques), salle de fête, chapiteaux, cour de la paroisse',
                'ordre' => 3,
            ],
            [
                'code' => 'popote_subvention',
                'nom' => 'Popote / Subvention',
                'description' => 'Subventions mensuelles récurrentes venant de la hiérarchie',
                'ordre' => 4,
            ],
            [
                'code' => 'procure',
                'nom' => 'Procure',
                'description' => 'Dîmes, denier du culte, casuel (baptêmes des enfants)',
                'ordre' => 5,
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
                        'actif' => true,
                        'ordre' => $category['ordre'],
                    ]
                );
                $count++;
            }
        }

        $this->command?->info("{$count} catégories de recettes créées ou mises à jour (toutes paroisses).");
    }
}
