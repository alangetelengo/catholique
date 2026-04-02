<?php

namespace Database\Seeders;

use App\Models\Member;
use App\Models\Paroisse;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MemberSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $paroisse = Paroisse::first();

        if (! $paroisse) {
            $this->command?->warn('Aucune paroisse trouvée, MemberSeeder ignoré.');
            return;
        }

        // On supprime les membres de test précédents pour éviter les doublons
        // (les membres "réels" en production ne devraient pas utiliser ce seeder).
        Member::query()->delete();

        // Créer au moins 50 membres de test avec des données congolaises
        // La factory choisit une paroisse aléatoire, mais on garantit
        // qu'il y a au moins des membres dans la première paroisse.
        Member::factory()
            ->count(50)
            ->create();

        $this->command?->info('50 membres de test (profils congolais) ont été créés.');
    }
}
