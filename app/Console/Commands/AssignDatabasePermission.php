<?php

namespace App\Console\Commands;

use App\Models\Role;
use App\Models\Permission;
use Illuminate\Console\Command;

class AssignDatabasePermission extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'permission:assign-database';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Assign manage database permission to superadmin and admin roles';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Find or create the manage database permission
        $permission = Permission::firstOrCreate(['name' => 'manage database']);
        
        // Assign to superadmin role
        $superadmin = Role::where('name', 'superadmin')->first();
        if ($superadmin && !$superadmin->hasPermissionTo('manage database')) {
            $superadmin->givePermissionTo('manage database');
            $this->info('Assigned manage database permission to superadmin role.');
        }
        
        // Assign to admin role
        $admin = Role::where('name', 'admin')->first();
        if ($admin && !$admin->hasPermissionTo('manage database')) {
            $admin->givePermissionTo('manage database');
            $this->info('Assigned manage database permission to admin role.');
        }
        
        $this->info('Database permission assignment completed successfully!');
    }
}
