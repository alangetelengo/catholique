<?php

namespace Database\Seeders;

use App\Models\RevenueCategory;
use Illuminate\Database\Seeder;

class RevenueCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['code' => 'quete_ordinaire', 'nom' => 'Quête ordinaire', 'ordre' => 1],
            ['code' => 'procure', 'nom' => 'Procure', 'ordre' => 2],
            ['code' => 'location', 'nom' => 'Location', 'ordre' => 3],
            ['code' => 'autre', 'nom' => 'Autres revenus', 'ordre' => 4],
        ];

        foreach ($categories as $category) {
            RevenueCategory::query()->updateOrCreate(
                ['code' => $category['code']],
                [
                    'nom' => $category['nom'],
                    'ordre' => $category['ordre'],
                    'actif' => true,
                ]
            );
        }
    }
}
