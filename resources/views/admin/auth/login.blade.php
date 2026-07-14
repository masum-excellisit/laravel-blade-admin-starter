@extends('layouts.guest')
@section('title', 'Sign in')
@section('content')
<div class="lg:hidden mb-8 flex justify-center"><x-app-logo class="text-xl" /></div>
<h2 class="text-2xl font-bold text-slate-800">Welcome back</h2>
<p class="text-slate-500 mt-1 mb-8">Sign in to your admin account.</p>
<form method="POST" action="{{ route('admin.login.attempt') }}" class="space-y-5">
    @csrf
    <x-form.input name="email" type="email" label="Email address" required autofocus value="admin@example.com" />
    <x-form.input name="password" type="password" label="Password" required value="password" />
    <div class="flex items-center justify-between text-sm">
        <label class="flex items-center gap-2 text-slate-600">
            <input type="checkbox" name="remember" class="rounded border-slate-300 text-primary brand-ring">
            Remember me
        </label>
        <a href="{{ route('admin.password.request') }}" class="font-medium brand-gradient-text">Forgot password?</a>
    </div>
    <x-btn type="submit" class="w-full" size="lg">Sign in</x-btn>
</form>
<p class="mt-8 text-center text-xs text-slate-400">Demo: admin@example.com / password</p>
@endsection
