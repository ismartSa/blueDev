<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $superadmin = Role::create([
            'name'          => 'superadmin'
        ]);
        $superadmin->givePermissionTo([
            'delete user',
            'update user',
            'read user',
            'create user',
            'delete course',
            'update course',
            'read course',
            'create course',
            'delete role',
            'update role',
            'read role',
            'create role',
            'delete permission',
            'update permission',
            'read permission',
            'create permission'
        ]);
        $admin = Role::create([
            'name'          => 'admin'
        ]);
        $admin->givePermissionTo([
            'delete user',
            'update user',
            'read user',
            'create user',
            'read role',
            'read permission',
        ]);
        $operator = Role::create([
            'name'          => 'operator'
        ]);

        $operator->givePermissionTo([
            'read user',
            'create user',
            'read role',
            'read permission',
        ]);
        $instructor = Role::create([
            'name' => 'instructor'
        ]);
        
        $instructor->givePermissionTo([
            'create course',
            'update course',
            'read course',
            'read quiz',
            'create quiz',
            'view quiz reports'
        ]);

        // Student role
        $student = Role::create([
            'name' => 'student'
        ]);
        $student->givePermissionTo([
            'read course',
            'read quiz'
        ]);
        
    }
}
