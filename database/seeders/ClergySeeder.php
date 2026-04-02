<?php

namespace Database\Seeders;

use App\Models\Member;
use App\Models\Paroisse;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ClergySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $paroisse = Paroisse::first();

        if (! $paroisse) {
            $this->command?->warn('Aucune paroisse trouvée, ClergySeeder ignoré.');
            return;
        }

        $clergy = [
            ['prenom' => 'Jean', 'nom' => 'DUPONT', 'notes' => 'Curé de la paroisse'],
            ['prenom' => 'Pierre', 'nom' => 'MABIALA', 'notes' => 'Abbé - vicaire'],
            ['prenom' => 'André', 'nom' => 'NGOMA', 'notes' => 'Père spiritain'],
        ];

        foreach ($clergy as $data) {
            Member::create([
                'prenom' => $data['prenom'],
                'nom' => $data['nom'],
                'date_naissance' => now()->subYears(40),
                'sexe' => 'M',
                'adresse' => $paroisse->adresse ?? null,
                'telephone' => null,
                'email' => null,
                'statut' => 'actif',
                'notes' => $data['notes'],
                'paroisse_id' => $paroisse->id,
            ]);
        }

        $this->command?->info('Curé / abbé / père créés avec succès (ClergySeeder).');
    }
}
