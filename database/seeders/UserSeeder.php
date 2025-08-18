<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $superadmin = User::firstOrCreate(
            ['email' => 'superadmin@superadmin.com'],
            [
                'name'              => 'Superadmin',
                'password'          => bcrypt('superadmin'),
                'email_verified_at' => date('Y-m-d H:i')
            ]
        );
        $superadmin->assignRole('superadmin');

        $admin = User::firstOrCreate(
            ['email' => 'admin@admin.com'],
            [
                'name'              => 'Admin',
                'password'          => bcrypt('admin'),
                'email_verified_at' => date('Y-m-d H:i')
            ]
        );
        $admin->assignRole('admin');

        $student = User::firstOrCreate(
            ['email' => 'student@student.com'],
            [
                'name'              => 'student',
                'password'          => bcrypt('student'),
                'email_verified_at' => date('Y-m-d H:i')
            ]
        );
        $student->assignRole('student');
    }
}
