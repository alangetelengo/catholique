<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminAccessSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::query()->updateOrCreate(
            ['email' => 'admin@catholique.local'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
            ]
        );

        $role = Role::query()->where('name', 'super_admin')->first();
        if ($role) {
            $user->syncRoles([$role->name]);
        }
    }
}
