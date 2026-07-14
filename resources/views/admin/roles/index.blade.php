@extends('layouts.admin')
@section('title', 'Roles')
@section('content')
<x-page-header title="Roles" subtitle="Group permissions into roles.">
    <x-slot:actions>@can('roles.create')<x-btn :href="route('admin.roles.create')">+ New role</x-btn>@endcan</x-slot:actions>
</x-page-header>
<x-table :headings="['Role', 'Users', 'Permissions', '']">
    @foreach($roles as $role)
    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/60">
        <td class="px-4 py-3 font-medium">{{ $role->name }}</td>
        <td class="px-4 py-3">{{ $role->users_count }}</td>
        <td class="px-4 py-3">{{ $role->permissions_count }}</td>
        <td class="px-4 py-3 text-right whitespace-nowrap">
            @can('roles.edit')<x-btn size="sm" variant="ghost" :href="route('admin.roles.edit', $role)">Edit</x-btn>@endcan
            @can('roles.delete')@if($role->name !== 'super-admin')
            <form method="POST" action="{{ route('admin.roles.destroy', $role) }}" class="inline" onsubmit="return confirm('Delete role?')">@csrf @method('DELETE')
                <x-btn size="sm" variant="ghost" type="submit" class="!text-red-600">Delete</x-btn></form>
            @endif @endcan
        </td>
    </tr>
    @endforeach
</x-table>
@endsection
