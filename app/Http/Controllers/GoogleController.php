<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;

class GoogleController extends Controller
{

    public function redirectToProvider()
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleProviderCallback()
    {
        try {

            $user = Socialite::driver("google")->user();

            // Check Users Email If Already There
            $is_user = User::where('email', $user->getEmail())->first();

            if (!$is_user) {

                $saveUser = User::updateOrCreate([
                    'google_id' => $user->getId(),
                ], [
                    'name' => $user->getName(),
                    'email' => $user->getEmail(),
                    'avatar' => $user->avatar,
                    'password' => Hash::make('password')
                ]);
            } else {
                $saveUser = User::where('email',  $user->getEmail())->update([
                    'google_id' => $user->getId(),
                    'name' => $user->getName(),
                    'avatar' => $user->avatar,
                ]);
                $saveUser = User::where('email', $user->getEmail())->first();
            }

            Auth::loginUsingId($saveUser->id);

            return redirect(env('FRONTEND_URL'));
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}
