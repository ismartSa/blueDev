<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserInsertTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_create_user_with_valid_data()
    {
        $userData = [
            'name' => 'أحمد محمد',
            'email' => 'ahmed@example.com',
            'password' => 'password123',
        ];

        $user = User::create([
            'name' => $userData['name'],
            'email' => $userData['email'],
            'password' => bcrypt($userData['password']),
        ]);

        $this->assertDatabaseHas('users', [
            'name' => 'أحمد محمد',
            'email' => 'ahmed@example.com',
        ]);
        $this->assertNotNull($user->id);
    }

    /** @test */
    public function can_create_multiple_users()
    {
        $users = User::factory()->count(5)->create();

        $this->assertCount(5, $users);
        $this->assertEquals(5, User::count());
    }
}
