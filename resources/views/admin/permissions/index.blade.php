@extends('layouts.admin')
@section('title', 'Permissions')
@section('content')
<x-page-header title="Permissions" subtitle="Low-level access grants, grouped by module.">
    <x-slot:actions>@can('permissions.create')<x-btn :href="route('admin.permissions.create')"><x-icon name="plus" class="w-4 h-4" /> New permission</x-btn>@endcan</x-slot:actions>
</x-page-header>

<x-permission-modules :modules="$modules" searchable />
@endsection
