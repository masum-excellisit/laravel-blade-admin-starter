@extends('layouts.admin')
@section('title', 'Profile')
@section('content')
<x-page-header title="My profile" subtitle="Manage your personal details and password." />
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 max-w-4xl">
    <x-card title="Details">
        <form method="POST" action="{{ route('admin.profile.update') }}" enctype="multipart/form-data" class="space-y-5">
            @csrf @method('PUT')
            <x-form.image name="avatar" label="Avatar" rounded="rounded-full"
                :current="$user->avatar ? \Illuminate\Support\Facades\Storage::disk('public')->url($user->avatar) : ''" />
            <x-form.input name="name" label="Name" :value="$user->name" required />
            <x-form.input name="email" type="email" label="Email" :value="$user->email" required />
            <x-form.input name="phone" label="Phone" :value="$user->phone" />
            <x-btn type="submit">Save changes</x-btn>
        </form>
    </x-card>
    <x-card title="Change password">
        <form method="POST" action="{{ route('admin.profile.password') }}" class="space-y-5">
            @csrf @method('PUT')
            <x-form.input name="current_password" type="password" label="Current password" required />
            <x-form.input name="password" type="password" label="New password" required />
            <x-form.input name="password_confirmation" type="password" label="Confirm new password" required />
            <x-btn type="submit">Update password</x-btn>
        </form>
    </x-card>
</div>
@endsection
