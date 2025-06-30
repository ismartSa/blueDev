<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $permissions = [
            'delete user', 'update user', 'read user', 'create user',
            'manage courses', 'manage roles', 'manage permissions', 'manage users',
            'manage quizzes', 'manage quiz reports', 'manage quiz results',
            'delete course', 'update course', 'read course', 'create course',
            'delete role', 'update role', 'read role', 'create role',
            'read quiz', 'create quiz', 'view quiz reports',
            'delete permission', 'update permission', 'read permission', 'create permission',
            'read category', 'create category', 'update category', 'delete category'
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }
    }
}
