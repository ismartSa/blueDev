<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class GoogleController extends Controller
{
    /**
     * Redirect the user to the Google authentication page.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function redirectToGoogle()
    {

        return Socialite::driver('google')->redirect();
    }

    /**
     * Obtain the user information from Google.
     *
     * @return \Illuminate\Http\RedirectResponse
     */

    public function handleGoogleCallback()
{
    try {
        DB::beginTransaction();
        $user = Socialite::driver('google')->stateless()->user();

        // Check if the user already exists with the Google ID
        $existingUser = User::where('google_id', $user->id)->first();

        if ($existingUser) {
            // User exists, log them in
            Auth::login($existingUser);
            DB::commit();
            return redirect()->intended('dashboard');
        } else {
            // Check if a user exists with the same email (but no Google ID)
            $emailExists = User::where('email', $user->email)->first();

            if ($emailExists) {
                // User exists with email, update Google ID
                $emailExists->google_id = $user->id;
                $emailExists->save();

                Auth::login($emailExists);
                DB::commit();
                return redirect()->intended('dashboard');
            } else {
                // Create a new user
                $newUser = new User;
                $newUser->name = $user->name;
                $newUser->email = $user->email;
            
                $newUser->password = bcrypt(request(Str::random()));
                $newUser->google_id = $user->id;
                $newUser->assignRole('operator');
                $newUser->save();

                Auth::login($newUser);
                DB::commit();
                return redirect()->intended('dashboard');
            }
        }
    } catch (\Exception $e) {
        DB::rollback();
        return redirect()->route('login')->with('error', __('app.label.created_error', ['name' => __('app.label.user')]) . $e->getMessage());
    }
}
}
