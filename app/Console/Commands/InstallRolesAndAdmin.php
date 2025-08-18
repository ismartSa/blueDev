<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

class InstallRolesAndAdmin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'install:roles-admin {--force : Force reinstall even if admin exists}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install roles and create admin user for the application';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Installing roles and admin user...');
        
        // Create roles if they don't exist
        $roles = ['admin', 'instructor', 'student'];
        $createdRoles = [];
        
        foreach ($roles as $roleName) {
            $role = Role::firstOrCreate(['name' => $roleName]);
            if ($role->wasRecentlyCreated) {
                $createdRoles[] = $roleName;
            }
        }
        
        if (!empty($createdRoles)) {
            $this->info('Created roles: ' . implode(', ', $createdRoles));
        } else {
            $this->info('All roles already exist.');
        }
        
        // Check if admin user exists
        $adminExists = User::where('email', 'superadmin@superadmin.com')->exists();
        
        if ($adminExists && !$this->option('force')) {
            $this->warn('Admin user already exists. Use --force to recreate.');
            return;
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
        $adminUser->syncRoles(['admin']);
        
        $this->info('✅ Admin user created/updated successfully!');
        $this->info('📧 Email: superadmin@superadmin.com');
        $this->info('🔑 Password: superadmin');
        $this->info('🎯 Role: admin');
        
        $this->newLine();
        $this->info('🚀 You can now login to the application with these credentials.');
        
        return Command::SUCCESS;
    }
}
