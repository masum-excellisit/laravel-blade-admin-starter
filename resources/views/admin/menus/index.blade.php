@extends('layouts.admin')
@section('title', 'Menus')
@section('content')
<x-page-header title="Menus" subtitle="Build navigation for the public site.">
    <x-slot:actions>@can('menus.create')<x-btn :href="route('admin.menus.create')"><x-icon name="plus" class="w-4 h-4" /> New menu</x-btn>@endcan</x-slot:actions>
</x-page-header>

<x-search placeholder="Search menus…" />

<x-table bulk :columns="[
    ['key' => 'name', 'label' => 'Name', 'sortable' => true],
    ['key' => 'location', 'label' => 'Location', 'sortable' => true],
    ['key' => null, 'label' => 'Items', 'sortable' => false],
    ['key' => null, 'label' => '', 'sortable' => false],
]">
    <x-slot:toolbar>
        <x-bulk-actions :action="route('admin.menus.bulk')" :options="['delete' => 'Delete selected']" />
    </x-slot:toolbar>
    @forelse($menus as $menu)
    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/60">
        <x-table-checkbox :id="$menu->id" />
        <td class="px-4 py-3 font-medium">{{ $menu->name }}</td>
        <td class="px-4 py-3">@if($menu->location)<x-badge color="indigo">{{ $menu->location }}</x-badge>@else<span class="text-slate-400">—</span>@endif</td>
        <td class="px-4 py-3">{{ $menu->items_count }}</td>
        <td class="px-4 py-3">
            <div class="flex items-center justify-end gap-1">
                @can('menus.edit')<x-icon-btn icon="edit" :href="route('admin.menus.edit', $menu)" label="Edit" />@endcan
                @can('menus.delete')<form method="POST" action="{{ route('admin.menus.destroy', $menu) }}" onsubmit="return confirm('Delete menu?')">@csrf @method('DELETE')<x-icon-btn icon="trash" type="submit" variant="danger" label="Delete" /></form>@endcan
            </div>
        </td>
    </tr>
    @empty<tr><td colspan="5" class="px-4 py-12 text-center text-slate-400">No menus yet.</td></tr>@endforelse
</x-table>
{{ $menus->links() }}
@endsection
