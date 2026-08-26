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
            // Banque (trésorerie générale)
            ['category_code' => 'banque', 'code' => 'revenu_principal', 'nom' => 'Revenu principal', 'description' => 'Revenu principal reçu de la hiérarchie', 'ordre' => 1, 'actif' => true],
            // Quête ordinaire
            ['category_code' => 'quete_ordinaire', 'code' => 'messe_semaine', 'nom' => 'Messe semaine', 'description' => 'Messes du lundi au samedi', 'ordre' => 1],
            ['category_code' => 'quete_ordinaire', 'code' => 'messe_dimanche', 'nom' => 'Messe dimanche', 'description' => 'Messe du dimanche', 'ordre' => 2],
            // Quête extraordinaire
            ['category_code' => 'quete_extraordinaire', 'code' => 'mariage', 'nom' => 'Mariage', 'description' => 'Recette de mariage', 'ordre' => 1],
            ['category_code' => 'quete_extraordinaire', 'code' => 'obseques', 'nom' => 'Obsèques', 'description' => 'Recette d\'obsèques', 'ordre' => 2],
            ['category_code' => 'quete_extraordinaire', 'code' => 'action_grace', 'nom' => 'Action de grâce', 'description' => 'Action de grâce (anniversaire, etc.)', 'ordre' => 3],
            ['category_code' => 'quete_extraordinaire', 'code' => 'caritas', 'nom' => 'Caritas', 'description' => 'Recette Caritas', 'ordre' => 4],
            ['category_code' => 'quete_extraordinaire', 'code' => 'grotte', 'nom' => 'La grotte', 'description' => 'Recette de la grotte', 'ordre' => 5],
            ['category_code' => 'quete_extraordinaire', 'code' => 'autre_extraordinaire', 'nom' => 'Autre extraordinaire', 'description' => 'Autre recette extraordinaire', 'ordre' => 6],
            ['category_code' => 'quete_extraordinaire', 'code' => 'nsinsani', 'nom' => 'NSINSANI', 'description' => null, 'ordre' => 10],
            // Location
            ['category_code' => 'location', 'code' => 'loyer_boutique', 'nom' => 'Loyer boutique', 'description' => 'Loyer d\'une boutique', 'ordre' => 1],
            ['category_code' => 'location', 'code' => 'salle_fete', 'nom' => 'Salle de fête', 'description' => 'Location de salle de fête', 'ordre' => 2],
            ['category_code' => 'location', 'code' => 'chapiteau', 'nom' => 'Chapiteau', 'description' => 'Location de chapiteau', 'ordre' => 3],
            ['category_code' => 'location', 'code' => 'cour_paroisse', 'nom' => 'Cour de la paroisse', 'description' => 'Location de la cour de la paroisse', 'ordre' => 4],
            // Subvention (obsolète — conservé inactif pour historique)
            ['category_code' => 'subvention', 'code' => 'subvention_carburant', 'nom' => 'Subvention carburant', 'description' => 'Obsolète — utiliser Caisse Transport', 'ordre' => 1, 'actif' => false],
            ['category_code' => 'subvention', 'code' => 'subvention_hosties', 'nom' => 'Subvention hosties', 'description' => 'Obsolète — utiliser Caisse Liturgie', 'ordre' => 2, 'actif' => false],
            ['category_code' => 'subvention', 'code' => 'subvention_gardiennage', 'nom' => 'Subvention gardiennage', 'description' => 'Obsolète — utiliser Caisse Salaires', 'ordre' => 3, 'actif' => false],
            ['category_code' => 'subvention', 'code' => 'subvention_gaz', 'nom' => 'Subvention gaz', 'description' => 'Obsolète — utiliser Caisse Charges', 'ordre' => 4, 'actif' => false],
            ['category_code' => 'subvention', 'code' => 'subvention_internet', 'nom' => 'Subvention internet', 'description' => 'Obsolète — utiliser Caisse Charges', 'ordre' => 5, 'actif' => false],
            ['category_code' => 'subvention', 'code' => 'subvention_eau', 'nom' => 'Subvention eau', 'description' => 'Obsolète — utiliser Caisse Charges', 'ordre' => 6, 'actif' => false],
            ['category_code' => 'subvention', 'code' => 'subvention_electricite', 'nom' => 'Subvention électricité', 'description' => 'Obsolète — utiliser Caisse Charges', 'ordre' => 7, 'actif' => false],
            ['category_code' => 'subvention', 'code' => 'subvention_salaires', 'nom' => 'Subvention salaires', 'description' => 'Obsolète — utiliser Caisse Salaires', 'ordre' => 8, 'actif' => false],
            ['category_code' => 'subvention', 'code' => 'subvention_popote', 'nom' => 'Subvention popote', 'description' => 'Obsolète — utiliser Caisse Alimentation / Popote', 'ordre' => 9, 'actif' => false],
            // Procure
            ['category_code' => 'procure', 'code' => 'dime', 'nom' => 'Dîme', 'description' => 'Dîmes', 'ordre' => 1],
            ['category_code' => 'procure', 'code' => 'denier_culte', 'nom' => 'Denier du culte', 'description' => 'Denier du culte', 'ordre' => 2],
            ['category_code' => 'procure', 'code' => 'casuel_bapteme', 'nom' => 'Casuel (baptêmes)', 'description' => 'Casuel pour les baptêmes des enfants', 'ordre' => 3],
            ['category_code' => 'procure', 'code' => 'don', 'nom' => 'Don', 'description' => 'Don à la paroisse', 'ordre' => 4],
            ['category_code' => 'procure', 'code' => 'nsinsani-d', 'nom' => 'NSINSANI diocésain', 'description' => null, 'ordre' => 5],
            ['category_code' => 'procure', 'code' => 'card', 'nom' => 'CARDINAL', 'description' => null, 'ordre' => 10],
            // Fête
            ['category_code' => 'fete', 'code' => 'fete-paroisse', 'nom' => 'Fête patronale paroissiale', 'description' => 'Anniversaire de la paroisse', 'ordre' => 1],
            ['category_code' => 'fete', 'code' => 'repas-doy', 'nom' => 'Repas du doyenné', 'description' => null, 'ordre' => 2],
            ['category_code' => 'fete', 'code' => 'renc-doy', 'nom' => 'Rencontre du doyenné', 'description' => null, 'ordre' => 3],
            ['category_code' => 'fete', 'code' => 'repas-natif', 'nom' => 'Repas des natifs', 'description' => null, 'ordre' => 4],
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
                        'actif' => $typeDef['actif'] ?? true,
                        'ordre' => $typeDef['ordre'],
                    ]
                );
                $count++;
            }
        }

        $this->command?->info("{$count} types de recettes créés ou mis à jour (toutes paroisses).");
    }
}
