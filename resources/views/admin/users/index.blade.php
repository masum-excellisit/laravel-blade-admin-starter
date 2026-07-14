@extends('layouts.admin')
@section('title', 'Admin Users')
@section('content')
<x-page-header title="Admin Users" subtitle="Staff accounts with panel access and roles.">
    <x-slot:actions>
        @can('users.create')<x-btn :href="route('admin.users.create')"><x-icon name="plus" class="w-4 h-4" /> New admin user</x-btn>@endcan
    </x-slot:actions>
</x-page-header>

<x-search placeholder="Search by name or email…" />

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
        <td class="px-4 py-3">
            <div class="flex items-center justify-end gap-1">
                @can('users.edit')<x-icon-btn icon="edit" :href="route('admin.users.edit', $user)" label="Edit" />@endcan
                @can('users.delete')
                <form method="POST" action="{{ route('admin.users.destroy', $user) }}" onsubmit="return confirm('Delete this user?')">@csrf @method('DELETE')
                    <x-icon-btn icon="trash" type="submit" variant="danger" label="Delete" /></form>
                @endcan
            </div>
        </td>
    </tr>
    @empty
    <tr><td colspan="5" class="px-4 py-12 text-center text-slate-400">No users found.</td></tr>
    @endforelse
</x-table>
{{ $users->links() }}
@endsection
