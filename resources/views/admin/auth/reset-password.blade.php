@extends('layouts.guest')
@section('title', 'Reset password')
@section('content')
<h2 class="text-2xl font-bold text-slate-800">Reset password</h2>
<p class="text-slate-500 mt-1 mb-8">Choose a new password.</p>
<form method="POST" action="{{ route('admin.password.update') }}" class="space-y-5">
    @csrf
    <input type="hidden" name="token" value="{{ $token }}">
    <x-form.input name="email" type="email" label="Email address" required :value="$email" />
    <x-form.input name="password" type="password" label="New password" required />
    <x-form.input name="password_confirmation" type="password" label="Confirm password" required />
    <x-btn type="submit" class="w-full" size="lg">Reset password</x-btn>
</form>
@endsection
