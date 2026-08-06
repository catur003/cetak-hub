<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class RoleAndAdminSeeder extends Seeder
{
    public function run(): void
    {
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        Role::firstOrCreate(['name' => 'customer']);

        $admin = User::firstOrCreate(
            ['email' => 'admin@cetakpro.test'],
            [
                'name' => 'Admin CetakPro',
                'password' => Hash::make('ChangeThisPassword123!'),
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        if (! $admin->hasRole('admin')) {
            $admin->assignRole($adminRole);
        }
    }
}
