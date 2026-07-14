@extends('layouts.guest')
@section('title', 'Sign in')
@section('content')
<div class="lg:hidden mb-10 flex flex-col items-center gap-3">
    <span class="h-14 w-14 rounded-2xl brand-gradient flex items-center justify-center text-white text-2xl shadow-lg">◆</span>
    <h1 class="text-xl font-bold">{{ $settings['site_name'] ?? config('app.name') }}</h1>
</div>
<form method="POST" action="{{ route('admin.login.attempt') }}" class="space-y-5">
    @csrf
    <x-form.input name="email" type="email" label="Email address" required autofocus />
    <x-form.input name="password" type="password" label="Password" required />
    <div class="flex items-center justify-between text-sm">
        <label class="flex items-center gap-2 text-slate-600">
            <input type="checkbox" name="remember" class="rounded border-slate-300 text-primary brand-ring">
            Remember me
        </label>
        <a href="{{ route('admin.password.request') }}" class="font-medium brand-gradient-text">Forgot password?</a>
    </div>
    <x-btn type="submit" class="w-full" size="lg">Sign in</x-btn>
</form>
@endsection
