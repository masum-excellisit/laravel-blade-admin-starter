@extends('layouts.app')
@section('title', 'Create account')
@section('content')
<section class="py-16">
    <div class="max-w-md mx-auto px-4 sm:px-6">
        <x-card title="Create your account">
            <form method="POST" action="{{ route('account.register.submit') }}" class="space-y-5">
                @csrf
                <x-form.input name="name" label="Name" required autofocus />
                <x-form.input name="email" type="email" label="Email address" required />
                <x-form.input name="password" type="password" label="Password" required />
                <x-form.input name="password_confirmation" type="password" label="Confirm password" required />
                <x-btn type="submit" class="w-full" size="lg">Register</x-btn>
            </form>
            <p class="mt-5 text-sm text-slate-500">
                Already have an account?
                <a href="{{ route('account.login') }}" class="font-semibold brand-gradient-text">Login</a>
            </p>
        </x-card>
    </div>
</section>
@endsection
