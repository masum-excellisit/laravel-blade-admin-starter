@extends('layouts.app')
@section('title', 'Customer login')
@section('content')
<section class="py-16">
    <div class="max-w-md mx-auto px-4 sm:px-6">
        <x-card title="Customer login">
            <form method="POST" action="{{ route('account.login.submit') }}" class="space-y-5">
                @csrf
                <x-form.input name="email" type="email" label="Email address" required autofocus />
                <x-form.input name="password" type="password" label="Password" required />
                <label class="flex items-center gap-2 text-sm text-slate-600">
                    <input type="checkbox" name="remember" class="rounded border-slate-300 text-primary brand-ring">
                    Remember me
                </label>
                <x-btn type="submit" class="w-full" size="lg">Login</x-btn>
            </form>
            <p class="mt-5 text-sm text-slate-500">
                New here?
                <a href="{{ route('account.register') }}" class="font-semibold brand-gradient-text">Create an account</a>
            </p>
        </x-card>
    </div>
</section>
@endsection
