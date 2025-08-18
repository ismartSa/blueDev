<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

class RoleAndAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create roles if they don't exist
        $roles = ['admin', 'instructor', 'student'];
        
        foreach ($roles as $roleName) {
            Role::firstOrCreate(['name' => $roleName]);
        }
        
        // Create or update admin user
        $adminUser = User::updateOrCreate(
            ['email' => 'superadmin@superadmin.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('superadmin'),
                'email_verified_at' => now(),
            ]
        );
        
        // Assign admin role to the user
        $adminUser->assignRole('admin');
        
        $this->command->info('Roles created and admin user set up successfully!');
        $this->command->info('Admin credentials: superadmin@superadmin.com / superadmin');
    }
}
