@extends('layouts.admin')
@section('title', 'Roles')
@section('content')
<x-page-header title="Roles" subtitle="Group permissions into roles.">
    <x-slot:actions>@can('roles.create')<x-btn :href="route('admin.roles.create')"><x-icon name="plus" class="w-4 h-4" /> New role</x-btn>@endcan</x-slot:actions>
</x-page-header>
<x-table :headings="['Role', 'Users', 'Permissions', '']">
    @foreach($roles as $role)
    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/60">
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
    @endforeach
</x-table>
@endsection
