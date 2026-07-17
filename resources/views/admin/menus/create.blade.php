@extends('layouts.admin')
@section('title', 'New menu')
@section('content')
@php $locations = ['header' => 'Header', 'footer' => 'Footer']; @endphp
<x-page-header title="New menu" subtitle="Create a navigation menu for a fixed site location." />
<x-card class="max-w-lg">
    <form method="POST" action="{{ route('admin.menus.store') }}" class="space-y-5">@csrf
        <x-form.input name="name" label="Menu name" required />
        <x-form.select name="location" label="Location" :options="$locations" :selected="old('location')" placeholder="Select location" required />
        <p class="text-xs text-slate-400 -mt-3">Header and footer are the fixed public-site locations.</p>
        <x-btn type="submit">Create menu</x-btn>
    </form>
</x-card>
@endsection
