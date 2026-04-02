<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Paroisse;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Créer ou mettre à jour le super admin
        $superAdmin = User::updateOrCreate(
            ['email' => 'admin@paroisse.cg'],
            [
                'name' => 'Super Administrateur',
                'username' => 'admin',
                'password' => Hash::make('password'),
                'paroisse_id' => null, // Super admin n'a pas de paroisse spécifique
            ]
        );

        $superAdminRole = Role::where('name', 'super_admin')->first();
        if ($superAdminRole) {
            $superAdmin->syncRoles([$superAdminRole->name]);
        }

        // Créer ou mettre à jour un admin de paroisse
        $paroisse = Paroisse::first();
        if ($paroisse) {
            $paroisseAdmin = User::updateOrCreate(
                ['email' => 'paroisse@paroisse.cg'],
                [
                    'name' => 'Administrateur Paroisse',
                    'username' => 'paroisse',
                    'password' => Hash::make('password'),
                    'paroisse_id' => $paroisse->id,
                ]
            );

            $paroisseAdminRole = Role::where('name', 'paroisse_admin')->first();
            if ($paroisseAdminRole) {
                $paroisseAdmin->syncRoles([$paroisseAdminRole->name]);
            }
        }

        $this->command->info('Utilisateurs admin créés ou mis à jour avec succès !');
        $this->command->info('Super Admin: admin@paroisse.cg / password');
        $this->command->info('Paroisse Admin: paroisse@paroisse.cg / password');
    }
}
