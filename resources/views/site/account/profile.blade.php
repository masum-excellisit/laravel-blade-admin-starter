@extends('layouts.app')
@section('title', 'My account')
@section('content')
<section class="py-16">
    <div class="max-w-2xl mx-auto px-4 sm:px-6">
        <x-card title="My account">
            <form method="POST" action="{{ route('account.profile.update') }}" class="space-y-5">
                @csrf
                @method('PUT')
                <x-form.input name="name" label="Name" :value="$user->name" required />
                <x-form.input name="email" type="email" label="Email address" :value="$user->email" required />
                <x-form.input name="phone" label="Phone" :value="$user->phone" />
                <x-btn type="submit">Save profile</x-btn>
            </form>
            <form method="POST" action="{{ route('account.logout') }}" class="mt-4">
                @csrf
                <x-btn type="submit" variant="outline">Logout</x-btn>
            </form>
        </x-card>
    </div>
</section>
@endsection
