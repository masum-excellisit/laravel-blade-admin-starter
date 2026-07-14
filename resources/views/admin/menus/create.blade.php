@extends('layouts.admin')
@section('title', 'New menu')
@section('content')
<x-page-header title="New menu" />
<x-card class="max-w-lg"><form method="POST" action="{{ route('admin.menus.store') }}" class="space-y-5">@csrf
    <x-form.input name="name" label="Menu name" required />
    <x-form.input name="location" label="Location" hint="e.g. header or footer — used to render on the site" />
    <x-btn type="submit">Create menu</x-btn>
</form></x-card>
@endsection
