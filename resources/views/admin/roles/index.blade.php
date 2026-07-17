@extends('layouts.admin')
@section('title', 'Roles')
@section('content')
<x-page-header title="Roles" subtitle="Group permissions into roles.">
    <x-slot:actions>@can('roles.create')<x-btn :href="route('admin.roles.create')"><x-icon name="plus" class="w-4 h-4" /> New role</x-btn>@endcan</x-slot:actions>
</x-page-header>

<x-search placeholder="Search roles…" />

<x-table bulk :columns="[
    ['key' => 'name', 'label' => 'Role', 'sortable' => true],
    ['key' => null, 'label' => 'Users', 'sortable' => false],
    ['key' => null, 'label' => 'Permissions', 'sortable' => false],
    ['key' => null, 'label' => '', 'sortable' => false],
]">
    <x-slot:toolbar>
        <x-bulk-actions :action="route('admin.roles.bulk')" :options="['delete' => 'Delete selected']" />
    </x-slot:toolbar>
    @forelse($roles as $role)
    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/60">
        <x-table-checkbox :id="$role->id" />
        <td class="px-4 py-3 font-medium capitalize">{{ $role->name }}</td>
        <td class="px-4 py-3">{{ $role->users_count }}</td>
        <td class="px-4 py-3">{{ $role->permissions_count }}</td>
        <td class="px-4 py-3">
            <div class="flex items-center justify-end gap-1">
                @can('roles.edit')<x-icon-btn icon="edit" :href="route('admin.roles.edit', $role)" label="Edit" />@endcan
                @can('roles.delete')@if($role->name !== 'super-admin')
                <form method="POST" action="{{ route('admin.roles.destroy', $role) }}" onsubmit="return confirm('Delete role?')">@csrf @method('DELETE')<x-icon-btn icon="trash" type="submit" variant="danger" label="Delete" /></form>
                @endif @endcan
            </div>
        </td>
    </tr>
    @empty
    <tr><td colspan="5" class="px-4 py-12 text-center text-slate-400">No roles found.</td></tr>
    @endforelse
</x-table>
{{ $roles->links() }}
@endsection
