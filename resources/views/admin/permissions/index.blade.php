@extends('layouts.admin')
@section('title', 'Permissions')
@section('content')
<x-page-header title="Permissions" subtitle="Low-level access grants, grouped by module.">
    <x-slot:actions>@can('permissions.create')<x-btn :href="route('admin.permissions.create')"><x-icon name="plus" class="w-4 h-4" /> New permission</x-btn>@endcan</x-slot:actions>
</x-page-header>

<x-search placeholder="Search permissions…" />

<x-table bulk :columns="[
    ['key' => 'name', 'label' => 'Permission', 'sortable' => true],
    ['key' => null, 'label' => 'Module', 'sortable' => false],
    ['key' => 'created_at', 'label' => 'Created', 'sortable' => true],
    ['key' => null, 'label' => '', 'sortable' => false],
]">
    <x-slot:toolbar>
        <x-bulk-actions :action="route('admin.permissions.bulk')" :options="['delete' => 'Delete selected']" />
    </x-slot:toolbar>
    @forelse($permissions as $perm)
    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/60">
        <x-table-checkbox :id="$perm->id" />
        <td class="px-4 py-3 font-mono text-sm">{{ $perm->name }}</td>
        <td class="px-4 py-3 capitalize text-slate-500">{{ explode('.', $perm->name)[0] ?? '—' }}</td>
        <td class="px-4 py-3 text-slate-500">{{ $perm->created_at?->format('M j, Y') ?? '—' }}</td>
        <td class="px-4 py-3">
            <div class="flex items-center justify-end gap-1">
                @can('permissions.edit')<x-icon-btn icon="edit" :href="route('admin.permissions.edit', $perm)" label="Edit" />@endcan
                @can('permissions.delete')<form method="POST" action="{{ route('admin.permissions.destroy', $perm) }}" onsubmit="return confirm('Delete permission?')">@csrf @method('DELETE')<x-icon-btn icon="trash" type="submit" variant="danger" label="Delete" /></form>@endcan
            </div>
        </td>
    </tr>
    @empty
    <tr><td colspan="5" class="px-4 py-12 text-center text-slate-400">No permissions found.</td></tr>
    @endforelse
</x-table>
{{ $permissions->links() }}
@endsection
