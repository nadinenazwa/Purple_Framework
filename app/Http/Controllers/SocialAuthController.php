<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Mail\OtpMail;

class SocialAuthController extends Controller
{
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->stateless()->redirect();
    }

    public function handleGoogleCallback()
    {
        $googleUser = Socialite::driver('google')->stateless()->user();

        $email = $googleUser->getEmail();
        $idGoogle = $googleUser->getId();
        $name = $googleUser->getName() ?? $googleUser->getNickname() ?? 'User';

        // find user by google id or email
        $user = User::where('id_google', $idGoogle)->orWhere('email', $email)->first();

        if (! $user) {
            // create user with a random hashed password (SSO)
            $user = User::create([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make(Str::random(24)),
                'id_google' => $idGoogle,
            ]);
        } else {
            if (! $user->id_google) {
                $user->id_google = $idGoogle;
                $user->save();
            }
        }

        // generate OTP
        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $user->otp = $otp;
        $user->save();

        // send otp email
        if ($user->email) {
            Mail::to($user->email)->send(new OtpMail($otp));
        }

        // store pending otp user id in session
        session(['otp_user_id' => $user->id]);

        return redirect()->route('otp.show');
    }
}
