<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AccountController extends Controller
{
    public function showLogin()
    {
        return view('site.account.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $attempt = Auth::attempt([
            'email' => $credentials['email'],
            'password' => $credentials['password'],
            'type' => User::TYPE_CUSTOMER,
            'status' => true,
        ], $request->boolean('remember'));

        if (! $attempt) {
            throw ValidationException::withMessages([
                'email' => 'These credentials do not match our customer records.',
            ]);
        }

        $request->session()->regenerate();
        Auth::user()->forceFill(['last_login_at' => now()])->save();

        return redirect()->intended(route('account.profile'));
    }

    public function showRegister()
    {
        return view('site.account.register');
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $customer = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'type' => User::TYPE_CUSTOMER,
            'password' => Hash::make($data['password']),
            'status' => true,
        ]);

        Auth::login($customer);
        $request->session()->regenerate();

        return redirect()->route('account.profile')->with('success', 'Welcome! Your account is ready.');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }

    public function profile(Request $request)
    {
        if (! $request->user()->isCustomer()) {
            return redirect()->route('admin.dashboard');
        }

        return view('site.account.profile', ['user' => $request->user()]);
    }

    public function updateProfile(Request $request)
    {
        if (! $request->user()->isCustomer()) {
            return redirect()->route('admin.dashboard');
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($request->user()->id),
            ],
            'phone' => ['nullable', 'string', 'max:50'],
        ]);

        $request->user()->update($data);

        return back()->with('success', 'Profile updated.');
    }
}
