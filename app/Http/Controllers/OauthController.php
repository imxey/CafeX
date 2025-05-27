<?php

namespace App\Http\Controllers;

use Auth;
use Laravel\Socialite\Facades\Socialite;
use Exception;
use App\Models\User;
use Illuminate\Support\Str;

class OauthController extends Controller
{
    public function redirectToProvider($provider)
    {
        return Socialite::driver($provider)->redirect();
    }

    public function handleProviderCallback($provider)
    {
        try {

            $user = Socialite::driver($provider)->user();

            $finduser = User::where('email', $user->email ?? $user->nickname)->first();

            if ($finduser) {
                Auth::login($finduser);
                return redirect(route('dashboard'));
            } else {
                $newUser = User::create([
                    'name' => $user->name ?? $user->nickname,
                    'email' => $user->email ?? $user->nickname,
                    'gauth_id' => $user->id,
                    'gauth_type' => $provider,
                    'password' => bcrypt('password'),
                ]);

                Auth::login($newUser);

                return redirect(route('dashboard'));
            }
        } catch (Exception $e) {
            dd("Error: " . $e->getMessage());
        }
    }
}
