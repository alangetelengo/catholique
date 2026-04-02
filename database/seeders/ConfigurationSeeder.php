<?php

namespace Database\Seeders;

use App\Models\Configuration;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ConfigurationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Configuration::updateOrCreate(
            ['paroisse_id' => 1, 'cle' => 'loader_actif'],
            ['valeur' => '0', 'type' => 'boolean', 'description' => null, 'actif' => true]
        );
        Configuration::updateOrCreate(
            ['paroisse_id' => 1, 'cle' => 'loader_duree_min'],
            ['valeur' => '1', 'type' => 'string', 'description' => null, 'actif' => true]
        );
        Configuration::updateOrCreate(
            ['paroisse_id' => 1, 'cle' => 'loader_afficher_logo'],
            ['valeur' => '0', 'type' => 'boolean', 'description' => null, 'actif' => true]
        );
        Configuration::updateOrCreate(
            ['paroisse_id' => 1, 'cle' => 'loader_style'],
            ['valeur' => 'logo_spinner', 'type' => 'string', 'description' => null, 'actif' => true]
        );
        Configuration::updateOrCreate(
            ['paroisse_id' => 1, 'cle' => 'loader_position'],
            ['valeur' => 'centre', 'type' => 'string', 'description' => null, 'actif' => true]
        );
        Configuration::updateOrCreate(
            ['paroisse_id' => 1, 'cle' => 'loader_couleur_fond'],
            ['valeur' => '#003366', 'type' => 'string', 'description' => null, 'actif' => true]
        );
        Configuration::updateOrCreate(
            ['paroisse_id' => 1, 'cle' => 'loader_couleur_texte'],
            ['valeur' => '#ffffff', 'type' => 'string', 'description' => null, 'actif' => true]
        );
    }
}

