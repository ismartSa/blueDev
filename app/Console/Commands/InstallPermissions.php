<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class InstallPermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'install:permissions {--force : Force reinstall permissions}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install all permissions and assign them to roles';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $force = $this->option('force');

        // Define all permissions
        $permissions = [
            // User permissions
            'delete user',
            'update user',
            'read user',
            'create user',

            // Course permissions
            'manage courses',
            'delete course',
            'update course',
            'read course',
            'create course',

            // Role permissions
            'delete role',
            'update role',
            'read role',
            'create role',

            // Permission permissions
            'delete permission',
            'update permission',
            'read permission',
            'create permission',

            // Quiz permissions
            'read quiz',
            'create quiz',
            'view quiz reports',

            // Database permissions
            'manage database'
        ];

        $this->info('Installing permissions...');

        // Create permissions
        foreach ($permissions as $permission) {
            if ($force) {
                Permission::updateOrCreate(['name' => $permission]);
            } else {
                Permission::firstOrCreate(['name' => $permission]);
            }
        }

        $this->info('Permissions created successfully.');

        // Define role permissions mapping
        $rolePermissions = [
            'superadmin' => [
                'delete user', 'update user', 'read user', 'create user',
                'manage courses', 'delete course', 'update course', 'read course', 'create course',
                'delete role', 'update role', 'read role', 'create role',
                'delete permission', 'update permission', 'read permission', 'create permission',
                'manage database'
            ],
            'admin' => [
              'delete user', 'update user', 'read user', 'create user',
                'manage courses', 'delete course', 'update course', 'read course', 'create course',
                'delete role', 'update role', 'read role', 'create role',
                'delete permission', 'update permission', 'read permission', 'create permission',
                'manage database'
            ],
            'operator' => [
                'read user', 'create user', 'read role', 'read permission'
            ],
            'instructor' => [
                'create course', 'update course', 'read course',
                'read quiz', 'create quiz', 'view quiz reports'
            ],
            'student' => [
                'read course', 'read quiz'
            ]
        ];

        $this->info('Assigning permissions to roles...');

        // Create roles and assign permissions
        foreach ($rolePermissions as $roleName => $rolePerms) {
            $role = Role::firstOrCreate(['name' => $roleName]);

            if ($force) {
                $role->syncPermissions($rolePerms);
                $this->info("Role '{$roleName}' permissions updated.");
            } else {
                $role->givePermissionTo($rolePerms);
                $this->info("Role '{$roleName}' permissions assigned.");
            }
        }

        $this->info('All permissions and roles have been set up successfully!');

        if (!$force) {
            $this->warn('Use --force flag to update existing permissions and roles.');
        }

        return Command::SUCCESS;
    }
}
