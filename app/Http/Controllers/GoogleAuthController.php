<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();

            $user = User::where('google_id', $googleUser->id)->first();

            if ($user) {
                Auth::login($user);
                return redirect()->intended('beranda');
            } else {
                $user = User::where('email', $googleUser->email)->first();

                if ($user) {
                    $user->update([
                        'google_id' => $googleUser->id,
                    ]);
                    Auth::login($user);
                    return redirect()->intended('beranda');
                } else {
                    $newUser = User::create([
                        'name' => $googleUser->name,
                        'email' => $googleUser->email,
                        'google_id' => $googleUser->id,
                        'password' => Hash::make(Str::random(16)),
                        'email_verified_at' => now(), // Assume verified by Google
                    ]);

                    // Assign default role (User/Member) - check CreateNewUser logic
                    // CreateNewUser assigns role 2.
                    $newUser->assignRole(2);

                    Auth::login($newUser);
                    return redirect()->intended('beranda');
                }
            }
        } catch (\Exception $e) {
            // Log error or show message
            return redirect()->route('login')->with('status', 'Login failed: ' . $e->getMessage());
        }
    }
}
