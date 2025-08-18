<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RegisteredUserControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create student role for testing
        Role::create(['name' => 'student']);
    }

    /** @test */
    public function user_can_register_successfully()
    {
        Event::fake();
        
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->post('/register', $userData);

        // Assert user was created
        $this->assertDatabaseHas('users', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);

        // Assert user was assigned student role
        $user = User::where('email', 'john@example.com')->first();
        $this->assertTrue($user->hasRole('student'));

        // Assert password was hashed
        $this->assertTrue(Hash::check('password123', $user->password));

        // Assert events were fired
        Event::assertDispatched(Registered::class);

        // Assert user was logged in and redirected
        $this->assertAuthenticated();
        $response->assertRedirect(RouteServiceProvider::HOME);
    }

    /** @test */
    public function registration_fails_with_invalid_data()
    {
        $testCases = [
            // Missing name
            [
                'data' => ['email' => 'test@example.com', 'password' => 'password123', 'password_confirmation' => 'password123'],
                'error' => 'name'
            ],
            // Invalid email
            [
                'data' => ['name' => 'Test User', 'email' => 'invalid-email', 'password' => 'password123', 'password_confirmation' => 'password123'],
                'error' => 'email'
            ],
            // Password confirmation mismatch
            [
                'data' => ['name' => 'Test User', 'email' => 'test@example.com', 'password' => 'password123', 'password_confirmation' => 'different'],
                'error' => 'password'
            ],
            // Duplicate email
            [
                'data' => ['name' => 'Test User', 'email' => 'existing@example.com', 'password' => 'password123', 'password_confirmation' => 'password123'],
                'error' => 'email',
                'setup' => fn() => User::factory()->create(['email' => 'existing@example.com'])
            ]
        ];

        foreach ($testCases as $case) {
            if (isset($case['setup'])) {
                $case['setup']();
            }

            $response = $this->post('/register', $case['data']);
            $response->assertSessionHasErrors($case['error']);
            $this->assertGuest();
        }
    }

    /** @test */
    public function registration_handles_validation_errors_properly()
    {
        // Test with invalid email format to trigger validation error
        $userData = [
            'name' => 'John Doe',
            'email' => 'invalid-email-format',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->post('/register', $userData);

        // Assert validation error handling
        $response->assertSessionHasErrors('email');
        $response->assertSessionHasInput('name', 'John Doe');
        $response->assertSessionHasInput('email', 'invalid-email-format');
        // Password fields should not be included in old input for security
        
        // Assert user was not created
        $this->assertDatabaseMissing('users', ['name' => 'John Doe']);
        $this->assertGuest();
    }

    /** @test */
    public function registration_requires_all_fields()
    {
        $response = $this->post('/register', []);
        
        $response->assertSessionHasErrors(['name', 'email', 'password']);
        $this->assertGuest();
    }

    /** @test */
    public function registration_enforces_password_rules()
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => '123', // Too short
            'password_confirmation' => '123',
        ];

        $response = $this->post('/register', $userData);
        
        $response->assertSessionHasErrors('password');
        $this->assertGuest();
    }

    /** @test */
    public function registration_enforces_name_length_limit()
    {
        $userData = [
            'name' => str_repeat('a', 256), // Too long
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->post('/register', $userData);
        
        $response->assertSessionHasErrors('name');
        $this->assertGuest();
    }

    /** @test */
    public function registration_enforces_email_length_limit()
    {
        $longEmail = str_repeat('a', 250) . '@example.com'; // Too long
        
        $userData = [
            'name' => 'John Doe',
            'email' => $longEmail,
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->post('/register', $userData);
        
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }
}