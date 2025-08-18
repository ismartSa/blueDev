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

        $operator = User::firstOrCreate(
            ['email' => 'operator@operator.com'],
            [
                'name'              => 'Operator',
                'password'          => bcrypt('operator'),
                'email_verified_at' => date('Y-m-d H:i')
            ]
        );
        $operator->assignRole('operator');
    }
}
