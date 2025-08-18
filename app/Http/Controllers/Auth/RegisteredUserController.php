<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;
use Illuminate\Validation\ValidationException;

class RegisteredUserController extends Controller
{
    private const DEFAULT_ROLE = 'student';
    private const PASSWORD_FIELDS = ['password', 'password_confirmation'];
    
    /**
     * Display the registration view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Register');
    }

    /**
     * Handle an incoming registration request.
     */
    public function store(Request $request): RedirectResponse
    {
        $validatedData = $this->validateRegistration($request);
        
        try {
            $user = $this->createUser($validatedData);
            $this->completeRegistration($user);
            
            return redirect(RouteServiceProvider::HOME);
        } catch (ValidationException $e) {
            // Re-throw validation exceptions to let Laravel handle them properly
            throw $e;
        } catch (Throwable $e) {
            return $this->handleRegistrationError($e, $request);
        }
    }
    
    /**
     * Validate registration request data.
     */
    private function validateRegistration(Request $request): array
    {
        return $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:' . User::class,
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);
    }
    
    /**
     * Create a new user with validated data.
     */
    private function createUser(array $data): User
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);
    }
    
    /**
     * Complete user registration process.
     */
    private function completeRegistration(User $user): void
    {
        $user->assignRole(self::DEFAULT_ROLE);
        event(new Registered($user));
        Auth::login($user);
    }
    
    /**
     * Handle registration errors with proper logging and user feedback.
     */
    private function handleRegistrationError(Throwable $e, Request $request): RedirectResponse
    {
        Log::error('User registration failed', [
            'error' => $e->getMessage(),
            'email' => $request->email,
            'trace' => $e->getTraceAsString()
        ]);
        
        $errorMessage = $this->getErrorMessage($e);
        
        return back()
            ->withErrors(['registration' => $errorMessage])
            ->withInput($request->except(self::PASSWORD_FIELDS));
    }
    
    /**
     * Get appropriate error message based on exception type.
     */
    private function getErrorMessage(Throwable $e): string
    {
        // Provide more specific error messages based on exception type
        if (str_contains($e->getMessage(), 'Duplicate entry')) {
            return 'An account with this email already exists.';
        }
        
        if (str_contains($e->getMessage(), 'Connection refused')) {
            return 'Database connection failed. Please try again later.';
        }
        
        return 'Registration failed. Please try again.';
    }
}
