@extends('layouts.admin')
@section('title', 'Content blocks')
@section('content')
<x-page-header title="Content blocks" subtitle="Reusable snippets rendered with the block() helper.">
    <x-slot:actions>@can('blocks.create')<x-btn :href="route('admin.blocks.create')"><x-icon name="plus" class="w-4 h-4" /> New block</x-btn>@endcan</x-slot:actions>
</x-page-header>

<x-search placeholder="Search blocks...">
    <x-slot:filters>
        <select name="active" onchange="this.form.submit()" class="rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm px-3.5 py-2.5 brand-ring shadow-sm">
            <option value="">All statuses</option>
            <option value="1" @selected(request('active') === '1')>Active</option>
            <option value="0" @selected(request('active') === '0')>Inactive</option>
        </select>
    </x-slot:filters>
</x-search>

<x-table bulk :columns="[
    ['key' => 'name', 'label' => 'Name', 'sortable' => true],
    ['key' => 'key', 'label' => 'Key', 'sortable' => true],
    ['key' => 'type', 'label' => 'Type', 'sortable' => true],
    ['key' => 'is_active', 'label' => 'Status', 'sortable' => true],
    ['key' => 'created_at', 'label' => 'Created', 'sortable' => true],
    ['label' => ''],
]">
    <x-slot:toolbar>
        @canany(['blocks.delete', 'blocks.edit'])
        <x-bulk-actions :action="route('admin.blocks.bulk')" :options="[
            'delete' => 'Delete selected',
            'activate' => 'Activate selected',
            'deactivate' => 'Deactivate selected',
        ]" />
        @endcanany
    </x-slot:toolbar>

    @forelse($blocks as $block)
    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/60">
        <x-table-checkbox :id="$block->id" />
        <td class="px-4 py-3 font-medium">{{ $block->name }}</td>
        <td class="px-4 py-3 text-slate-500"><code>{{ $block->key }}</code></td>
        <td class="px-4 py-3 text-slate-500">{{ $block->type }}</td>
        <td class="px-4 py-3"><x-badge :color="$block->is_active ? 'green' : 'slate'">{{ $block->is_active ? 'Active' : 'Inactive' }}</x-badge></td>
        <td class="px-4 py-3 text-slate-500">{{ $block->created_at->format('M j, Y') }}</td>
        <td class="px-4 py-3">
            <div class="flex items-center justify-end gap-1">
                @can('blocks.edit')<x-icon-btn icon="edit" :href="route('admin.blocks.edit', $block)" label="Edit" />@endcan
                @can('blocks.delete')<form method="POST" action="{{ route('admin.blocks.destroy', $block) }}" onsubmit="return confirm('Delete content block?')">@csrf @method('DELETE')<x-icon-btn icon="trash" type="submit" variant="danger" label="Delete" /></form>@endcan
            </div>
        </td>
    </tr>
    @empty
    <tr><td colspan="7" class="px-4 py-12 text-center text-slate-400">No content blocks yet.</td></tr>
    @endforelse
</x-table>
{{ $blocks->links() }}
@endsection
