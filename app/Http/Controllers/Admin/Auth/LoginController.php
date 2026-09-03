<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use App\Support\Activity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function show()
    {
        return view('admin.auth.login');
    }

    public function attempt(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $key = 'login:'.$request->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            throw ValidationException::withMessages([
                'email' => 'Too many attempts. Try again in '.RateLimiter::availableIn($key).' seconds.',
            ]);
        }

        if (! Auth::attempt($data, $request->boolean('remember'))) {
            RateLimiter::hit($key, 60);
            throw ValidationException::withMessages(['email' => 'These credentials do not match our records.']);
        }

        if (! Auth::user()->isAdmin()) {
            Auth::logout();
            throw ValidationException::withMessages(['email' => 'These credentials do not match our records.']);
        }

        if (! Auth::user()->status) {
            Auth::logout();
            throw ValidationException::withMessages(['email' => 'Your account is disabled.']);
        }

        RateLimiter::clear($key);
        $request->session()->regenerate();
        Auth::user()->forceFill(['last_login_at' => now()])->save();

        Activity::log('login', Auth::user(), 'Admin signed in');

        return redirect()->intended(route('admin.dashboard'));
    }

    public function logout(Request $request)
    {
        if (Auth::check()) {
            Activity::log('logout', Auth::user(), 'Admin signed out');
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}
