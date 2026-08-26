<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            // Base obligatoire
            ParoisseSeeder::class,

            // Référentiel FINANCES (prioritaire)
            RevenueCategorySeeder::class,
            RevenueTypeSeeder::class,
            CaisseSeeder::class,

            // Sécurité / accès
            RolePermissionSeeder::class,
            AdminUserSeeder::class,

            // Paramétrage complémentaire
            // ConfigurationSeeder::class,

            // Modules annexes (désactivés pour cette phase)
            // MemberSeeder::class,
            // ClergySeeder::class,
            // EventSeeder::class,
            // RevenueSeeder::class,
            // ExpenseSeeder::class,
        ]);
    }
}
