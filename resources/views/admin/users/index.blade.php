@extends('layouts.admin')
@section('title', 'Users')
@section('content')
<x-page-header title="Users" subtitle="Manage admin accounts and roles.">
    <x-slot:actions>
        @can('users.create')<x-btn :href="route('admin.users.create')">+ New user</x-btn>@endcan
    </x-slot:actions>
</x-page-header>

<form method="GET" class="mb-4">
    <input name="search" value="{{ request('search') }}" placeholder="Search users…" class="w-full sm:w-80 rounded-xl border border-slate-300 dark:border-slate-600 dark:bg-slate-800 text-sm px-4 py-2.5 brand-ring">
</form>

<x-table :headings="['User', 'Roles', 'Status', 'Last login', '']">
    @forelse($users as $user)
    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/60">
        <td class="px-4 py-3">
            <div class="flex items-center gap-3">
                <span class="h-9 w-9 rounded-full brand-gradient text-white text-xs font-semibold flex items-center justify-center">{{ $user->initials() }}</span>
                <div><p class="font-medium text-slate-800 dark:text-slate-100">{{ $user->name }}</p><p class="text-xs text-slate-400">{{ $user->email }}</p></div>
            </div>
        </td>
        <td class="px-4 py-3">@foreach($user->roles as $role)<x-badge color="indigo" class="mr-1">{{ $role->name }}</x-badge>@endforeach</td>
        <td class="px-4 py-3"><x-badge :color="$user->status ? 'green' : 'red'">{{ $user->status ? 'Active' : 'Disabled' }}</x-badge></td>
        <td class="px-4 py-3 text-slate-500">{{ $user->last_login_at?->diffForHumans() ?? '—' }}</td>
        <td class="px-4 py-3 text-right whitespace-nowrap">
            @can('users.edit')<x-btn size="sm" variant="ghost" :href="route('admin.users.edit', $user)">Edit</x-btn>@endcan
            @can('users.delete')
            <form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="inline" onsubmit="return confirm('Delete this user?')">@csrf @method('DELETE')
                <x-btn size="sm" variant="ghost" type="submit" class="!text-red-600">Delete</x-btn></form>
            @endcan
        </td>
    </tr>
    @empty
    <tr><td colspan="5" class="px-4 py-10 text-center text-slate-400">No users found.</td></tr>
    @endforelse
</x-table>
<div class="mt-4">{{ $users->links() }}</div>
@endsection
