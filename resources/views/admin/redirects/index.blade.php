@extends('layouts.admin')
@section('title', 'Redirects')
@section('content')
<x-page-header title="Redirects" subtitle="Manage URL redirects for moved or retired content.">
    <x-slot:actions>@can('redirects.create')<x-btn :href="route('admin.redirects.create')"><x-icon name="plus" class="w-4 h-4" /> New redirect</x-btn>@endcan</x-slot:actions>
</x-page-header>

<x-search placeholder="Search redirects...">
    <x-slot:filters>
        <select name="status" onchange="this.form.submit()" class="rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm px-3.5 py-2.5 brand-ring shadow-sm">
            <option value="">All statuses</option>
            <option value="active" @selected(request('status')==='active')>Active</option>
            <option value="inactive" @selected(request('status')==='inactive')>Inactive</option>
        </select>
    </x-slot:filters>
</x-search>

<x-table bulk :columns="[
    ['key' => 'from_path', 'label' => 'From', 'sortable' => true],
    ['key' => 'to_url', 'label' => 'To', 'sortable' => true],
    ['key' => 'status_code', 'label' => 'Status', 'sortable' => true],
    ['key' => 'is_active', 'label' => 'Active', 'sortable' => true],
    ['key' => 'hits', 'label' => 'Hits', 'sortable' => true],
    ['key' => 'created_at', 'label' => 'Created', 'sortable' => true],
    ['label' => ''],
]">
    <x-slot:toolbar>
        @canany(['redirects.delete', 'redirects.edit'])
        <x-bulk-actions :action="route('admin.redirects.bulk')" :options="[
            'delete' => 'Delete selected',
            'activate' => 'Activate selected',
            'deactivate' => 'Deactivate selected',
        ]" />
        @endcanany
    </x-slot:toolbar>

    @forelse($redirects as $redirect)
    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/60">
        <x-table-checkbox :id="$redirect->id" />
        <td class="px-4 py-3 font-medium">{{ $redirect->from_path }}</td>
        <td class="px-4 py-3 text-slate-500 max-w-xs truncate">{{ $redirect->to_url }}</td>
        <td class="px-4 py-3 text-slate-500">{{ $redirect->status_code }}</td>
        <td class="px-4 py-3"><x-badge :color="$redirect->is_active ? 'green' : 'slate'">{{ $redirect->is_active ? 'Active' : 'Inactive' }}</x-badge></td>
        <td class="px-4 py-3 text-slate-500">{{ number_format($redirect->hits) }}</td>
        <td class="px-4 py-3 text-slate-500">{{ $redirect->created_at->format('M j, Y') }}</td>
        <td class="px-4 py-3">
            <div class="flex items-center justify-end gap-1">
                @can('redirects.edit')<x-icon-btn icon="edit" :href="route('admin.redirects.edit', $redirect)" label="Edit" />@endcan
                @can('redirects.delete')<form method="POST" action="{{ route('admin.redirects.destroy', $redirect) }}" onsubmit="return confirm('Delete redirect?')">@csrf @method('DELETE')<x-icon-btn icon="trash" type="submit" variant="danger" label="Delete" /></form>@endcan
            </div>
        </td>
    </tr>
    @empty
    <tr><td colspan="8" class="px-4 py-12 text-center text-slate-400">No redirects yet.</td></tr>
    @endforelse
</x-table>
{{ $redirects->links() }}
@endsection
