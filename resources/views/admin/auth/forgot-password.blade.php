@extends('layouts.guest')
@section('title', 'Forgot password')
@section('content')
<h2 class="text-2xl font-bold text-slate-800">Forgot password?</h2>
<p class="text-slate-500 mt-1 mb-8">Enter your email and we'll send a reset link.</p>
<form method="POST" action="{{ route('admin.password.email') }}" class="space-y-5">
    @csrf
    <x-form.input name="email" type="email" label="Email address" required autofocus />
    <x-btn type="submit" class="w-full" size="lg">Send reset link</x-btn>
    <a href="{{ route('admin.login') }}" class="block text-center text-sm font-medium brand-gradient-text">Back to sign in</a>
</form>
@endsection
