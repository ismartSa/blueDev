<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RegisteredUserControllerErrorHandlingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create student role for testing
        Role::create(['name' => 'student']);
    }

    public function test_registration_provides_specific_error_for_duplicate_email(): void
    {
        // Create a user first
        User::factory()->create([
            'email' => 'test@example.com',
        ]);

        // Try to register with the same email
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_registration_excludes_password_from_old_input(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'invalid-email',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors('email');
        
        // Check old input using session
        $session = $response->getSession();
        $oldInput = $session->getOldInput();
        
        $this->assertArrayHasKey('name', $oldInput);
        $this->assertArrayHasKey('email', $oldInput);
        $this->assertArrayNotHasKey('password', $oldInput);
        $this->assertArrayNotHasKey('password_confirmation', $oldInput);
    }

    public function test_registration_assigns_student_role_by_default(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(RouteServiceProvider::HOME);
        $this->assertAuthenticated();
        
        $user = User::where('email', 'test@example.com')->first();
        $this->assertTrue($user->hasRole('student'));
    }
}