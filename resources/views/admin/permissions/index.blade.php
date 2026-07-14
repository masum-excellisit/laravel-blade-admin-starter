@extends('layouts.admin')
@section('title', 'Permissions')
@section('content')
<x-page-header title="Permissions" subtitle="Low-level access grants, grouped by module.">
    <x-slot:actions>@can('permissions.create')<x-btn :href="route('admin.permissions.create')">+ New permission</x-btn>@endcan</x-slot:actions>
</x-page-header>
<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
    @foreach($permissions as $module => $perms)
    <x-card :title="ucfirst($module)">
        <div class="space-y-2">
            @foreach($perms as $perm)
            <div class="flex items-center justify-between text-sm">
                <span class="text-slate-600 dark:text-slate-300">{{ $perm->name }}</span>
                <span class="flex gap-1">
                    @can('permissions.edit')<a href="{{ route('admin.permissions.edit', $perm) }}" class="text-xs brand-gradient-text">Edit</a>@endcan
                    @can('permissions.delete')<form method="POST" action="{{ route('admin.permissions.destroy', $perm) }}" onsubmit="return confirm('Delete?')">@csrf @method('DELETE')<button class="text-xs text-red-500">Del</button></form>@endcan
                </span>
            </div>
            @endforeach
        </div>
    </x-card>
    @endforeach
</div>
@endsection
