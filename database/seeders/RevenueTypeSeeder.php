<?php

namespace Database\Seeders;

use App\Models\Paroisse;
use App\Models\RevenueCategory;
use App\Models\RevenueType;
use Illuminate\Database\Seeder;

class RevenueTypeSeeder extends Seeder
{
    /**
     * Types de recettes par défaut, par catégorie (une copie par paroisse).
     */
    public function run(): void
    {
        $types = [
            // Quête ordinaire
            ['category_code' => 'quete_ordinaire', 'code' => 'messe_semaine', 'nom' => 'Messe Semaine', 'description' => 'Messes du lundi au samedi', 'ordre' => 1],
            ['category_code' => 'quete_ordinaire', 'code' => 'messe_dimanche', 'nom' => 'Messe Dimanche', 'description' => 'Messe du dimanche', 'ordre' => 2],
            // Quête extraordinaire
            ['category_code' => 'quete_extraordinaire', 'code' => 'mariage', 'nom' => 'Mariage', 'description' => 'Recette de mariage', 'ordre' => 1],
            ['category_code' => 'quete_extraordinaire', 'code' => 'obseques', 'nom' => 'Obsèques', 'description' => 'Recette d\'obsèques', 'ordre' => 2],
            ['category_code' => 'quete_extraordinaire', 'code' => 'action_grace', 'nom' => 'Action de Grâce', 'description' => 'Action de grâce (anniversaire, etc.)', 'ordre' => 3],
            ['category_code' => 'quete_extraordinaire', 'code' => 'caritas', 'nom' => 'Caritas', 'description' => 'Recette Caritas', 'ordre' => 4],
            ['category_code' => 'quete_extraordinaire', 'code' => 'grotte', 'nom' => 'La Grotte', 'description' => 'Recette de la grotte', 'ordre' => 5],
            ['category_code' => 'quete_extraordinaire', 'code' => 'autre_extraordinaire', 'nom' => 'Autre Extraordinaire', 'description' => 'Autre recette extraordinaire', 'ordre' => 6],
            ['category_code' => 'quete_extraordinaire', 'code' => 'nsinsani', 'nom' => 'NSINSANI', 'description' => null, 'ordre' => 10],
            // Location
            ['category_code' => 'location', 'code' => 'loyer_boutique', 'nom' => 'Loyer Boutique', 'description' => 'Loyer d\'une boutique', 'ordre' => 1],
            ['category_code' => 'location', 'code' => 'salle_fete', 'nom' => 'Salle de Fête', 'description' => 'Location de salle de fête', 'ordre' => 2],
            ['category_code' => 'location', 'code' => 'chapiteau', 'nom' => 'Chapiteau', 'description' => 'Location de chapiteau', 'ordre' => 3],
            ['category_code' => 'location', 'code' => 'cour_paroisse', 'nom' => 'Cour de la Paroisse', 'description' => 'Location de la cour de la paroisse', 'ordre' => 4],
            // Popote/Subvention
            ['category_code' => 'popote_subvention', 'code' => 'popote_mensuelle', 'nom' => 'Popote Mensuelle', 'description' => 'Popote mensuelle récurrente', 'ordre' => 1],
            ['category_code' => 'popote_subvention', 'code' => 'autre_subvention', 'nom' => 'Autre Subvention', 'description' => 'Autre subvention', 'ordre' => 2],
            // Procure
            ['category_code' => 'procure', 'code' => 'dime', 'nom' => 'Dîme', 'description' => 'Dîmes', 'ordre' => 1],
            ['category_code' => 'procure', 'code' => 'denier_culte', 'nom' => 'Denier du Culte', 'description' => 'Denier du culte', 'ordre' => 2],
            ['category_code' => 'procure', 'code' => 'casuel_bapteme', 'nom' => 'Casuel (Baptêmes)', 'description' => 'Casuel pour les baptêmes des enfants', 'ordre' => 3],
            ['category_code' => 'procure', 'code' => 'don', 'nom' => 'Don', 'description' => 'Don à la paroisse', 'ordre' => 4],
            ['category_code' => 'procure', 'code' => 'nsinsani-d', 'nom' => 'NSINSANI DIOCESAIN', 'description' => null, 'ordre' => 5],
            ['category_code' => 'procure', 'code' => 'card', 'nom' => 'CARDINAL', 'description' => null, 'ordre' => 10],
        ];

        $paroisses = Paroisse::all();
        if ($paroisses->isEmpty()) {
            $this->command?->warn('Aucune paroisse trouvée. RevenueTypeSeeder ignoré.');

            return;
        }

        $count = 0;
        foreach ($paroisses as $paroisse) {
            $categoriesByCode = RevenueCategory::where('paroisse_id', $paroisse->id)->get()->keyBy('code');
            foreach ($types as $typeDef) {
                $category = $categoriesByCode->get($typeDef['category_code']);
                if (! $category) {
                    $this->command?->warn("Catégorie {$typeDef['category_code']} absente pour la paroisse {$paroisse->nom}, type {$typeDef['code']} ignoré.");
                    continue;
                }
                RevenueType::updateOrCreate(
                    [
                        'paroisse_id' => $paroisse->id,
                        'code' => $typeDef['code'],
                    ],
                    [
                        'revenue_category_id' => $category->id,
                        'nom' => $typeDef['nom'],
                        'description' => $typeDef['description'],
                        'actif' => true,
                        'ordre' => $typeDef['ordre'],
                    ]
                );
                $count++;
            }
        }

        $this->command?->info("{$count} types de recettes créés ou mis à jour (toutes paroisses).");
    }
}
