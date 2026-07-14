@extends('layouts.admin')
@section('title', 'Permissions')
@section('content')
<x-page-header title="Permissions" subtitle="Low-level access grants, grouped by module.">
    <x-slot:actions>@can('permissions.create')<x-btn :href="route('admin.permissions.create')"><x-icon name="plus" class="w-4 h-4" /> New permission</x-btn>@endcan</x-slot:actions>
</x-page-header>

@php
$actionMeta = [
    'view' => ['icon' => 'eye', 'color' => 'text-sky-500', 'bg' => 'bg-sky-50 dark:bg-sky-900/20'],
    'create' => ['icon' => 'plus', 'color' => 'text-emerald-500', 'bg' => 'bg-emerald-50 dark:bg-emerald-900/20'],
    'edit' => ['icon' => 'edit', 'color' => 'text-amber-500', 'bg' => 'bg-amber-50 dark:bg-amber-900/20'],
    'delete' => ['icon' => 'trash', 'color' => 'text-rose-500', 'bg' => 'bg-rose-50 dark:bg-rose-900/20'],
];
$totalPerms = $permissions->flatten()->count();
@endphp

<div class="flex flex-wrap gap-3 mb-6">
    <div class="flex items-center gap-3 px-4 py-3 rounded-2xl bg-white dark:bg-slate-800/60 border border-slate-200/70 dark:border-slate-700/60 shadow-sm">
        <span class="h-10 w-10 rounded-xl brand-gradient text-white flex items-center justify-center"><x-icon name="check" class="w-5 h-5" /></span>
        <div><p class="text-2xl font-bold leading-none">{{ $totalPerms }}</p><p class="text-xs text-slate-400 mt-0.5">Total permissions</p></div>
    </div>
    <div class="flex items-center gap-3 px-4 py-3 rounded-2xl bg-white dark:bg-slate-800/60 border border-slate-200/70 dark:border-slate-700/60 shadow-sm">
        <span class="h-10 w-10 rounded-xl bg-primary-soft text-primary flex items-center justify-center"><x-icon name="filter" class="w-5 h-5" /></span>
        <div><p class="text-2xl font-bold leading-none">{{ $permissions->count() }}</p><p class="text-xs text-slate-400 mt-0.5">Modules</p></div>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
    @foreach($permissions as $module => $perms)
    <div class="group rounded-2xl bg-white dark:bg-slate-800/60 border border-slate-200/70 dark:border-slate-700/60 shadow-sm overflow-hidden hover:shadow-md transition">
        <div class="flex items-center justify-between px-5 py-3.5 border-b border-slate-100 dark:border-slate-700/60 brand-gradient-soft">
            <div class="flex items-center gap-2.5">
                <span class="h-8 w-8 rounded-lg brand-gradient text-white flex items-center justify-center text-sm font-bold">{{ strtoupper(substr($module, 0, 1)) }}</span>
                <h3 class="font-semibold capitalize">{{ $module }}</h3>
            </div>
            <x-badge color="indigo">{{ $perms->count() }}</x-badge>
        </div>
        <div class="divide-y divide-slate-100 dark:divide-slate-700/60">
            @foreach($perms as $perm)
            @php $action = explode('.', $perm->name)[1] ?? $perm->name; $m = $actionMeta[$action] ?? ['icon'=>'check','color'=>'text-slate-400','bg'=>'bg-slate-100 dark:bg-slate-700']; @endphp
            <div class="flex items-center gap-3 px-5 py-2.5 hover:bg-slate-50 dark:hover:bg-slate-800/80">
                <span class="h-7 w-7 shrink-0 rounded-lg {{ $m['bg'] }} {{ $m['color'] }} flex items-center justify-center"><x-icon name="{{ $m['icon'] }}" class="w-4 h-4" /></span>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium capitalize">{{ $action }}</p>
                    <p class="text-xs text-slate-400 font-mono truncate">{{ $perm->name }}</p>
                </div>
                <div class="flex items-center gap-0.5 opacity-0 group-hover:opacity-100 transition">
                    @can('permissions.edit')<x-icon-btn icon="edit" :href="route('admin.permissions.edit', $perm)" label="Edit" />@endcan
                    @can('permissions.delete')<form method="POST" action="{{ route('admin.permissions.destroy', $perm) }}" onsubmit="return confirm('Delete permission?')">@csrf @method('DELETE')<x-icon-btn icon="trash" type="submit" variant="danger" label="Delete" /></form>@endcan
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endforeach
</div>
@endsection
