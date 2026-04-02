<?php

namespace Database\Seeders;

use App\Models\Paroisse;
use Illuminate\Database\Seeder;

class ParoisseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Idempotent : si la paroisse existe déjà (même code), on la met à jour au lieu de recréer
        Paroisse::updateOrCreate(
            ['code_paroisse' => 'SEM001'],
            [
                'nom' => 'SAINT-ESPRIT DE MOUNGALI',
                'adresse' => 'Avenue de la Paix, Moungali',
                'ville' => 'Brazzaville',
                'pays' => 'République du Congo',
                'telephone' => '+242 06 XXX XX XX',
                'email' => 'contact@saint-esprit-moungali.cg',
                'diocèse' => 'Archidiocèse de Brazzaville',
                'description' => 'Paroisse de référence pour les tests',
                'actif' => true,
            ]
        );

        $this->command->info('Paroisse de test créée ou mise à jour avec succès !');
    }
}
