<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class OtpController extends Controller
{
    public function show()
    {
        // ensure there's a pending otp_user_id in session
        $userId = session('otp_user_id');
        if (! $userId) {
            return redirect()->route('login');
        }

        return view('auth.otp');
    }

    public function verify(Request $request)
    {
        $request->validate([
            'otp' => ['required', 'string', 'size:6'],
        ]);

        $userId = session('otp_user_id');
        if (! $userId) {
            return redirect()->route('login')->withErrors(['otp' => 'Session OTP tidak ditemukan. Silakan login ulang.']);
        }

        $user = User::find($userId);
        if (! $user) {
            return redirect()->route('login')->withErrors(['otp' => 'User tidak ditemukan.']);
        }

        if (! $user->otp || $user->otp !== $request->input('otp')) {
            return back()->withErrors(['otp' => 'Kode OTP tidak valid.'])->withInput();
        }

        // OTP valid: clear otp, log in user and redirect
        $user->otp = null;
        $user->save();

        Auth::login($user);
        $request->session()->forget('otp_user_id');

        return redirect()->route('dashboard');
    }
}
