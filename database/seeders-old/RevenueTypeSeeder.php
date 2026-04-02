<?php

namespace Database\Seeders;

use App\Models\RevenueCategory;
use App\Models\RevenueType;
use Illuminate\Database\Seeder;

class RevenueTypeSeeder extends Seeder
{
    public function run(): void
    {
        $definitions = [
            'quete_ordinaire' => [
                ['code' => 'quete-dominicale', 'nom' => 'Quête dominicale', 'ordre' => 1],
                ['code' => 'quete-semaine', 'nom' => 'Quête en semaine', 'ordre' => 2],
            ],
            'procure' => [
                ['code' => 'procure-messe', 'nom' => 'Procure de messe', 'ordre' => 1],
            ],
            'location' => [
                ['code' => 'loyer-boutique', 'nom' => 'Loyer boutique', 'ordre' => 1],
                ['code' => 'location-salle', 'nom' => 'Location de salle', 'ordre' => 2],
            ],
            'autre' => [
                ['code' => 'don-libre', 'nom' => 'Don libre', 'ordre' => 1],
            ],
        ];

        foreach ($definitions as $categoryCode => $types) {
            $category = RevenueCategory::query()->where('code', $categoryCode)->first();
            if (! $category) {
                continue;
            }

            foreach ($types as $type) {
                RevenueType::query()->updateOrCreate(
                    [
                        'revenue_category_id' => $category->id,
                        'code' => $type['code'],
                    ],
                    [
                        'nom' => $type['nom'],
                        'ordre' => $type['ordre'],
                        'actif' => true,
                    ]
                );
            }
        }
    }
}
