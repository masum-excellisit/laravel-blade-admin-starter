@extends('layouts.admin')
@section('title', 'Menus')
@section('content')
<x-page-header title="Menus" subtitle="Build navigation for the public site.">
    <x-slot:actions>@can('menus.create')<x-btn :href="route('admin.menus.create')">+ New menu</x-btn>@endcan</x-slot:actions>
</x-page-header>
<x-table :headings="['Name', 'Location', 'Items', '']">
    @forelse($menus as $menu)
    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/60">
        <td class="px-4 py-3 font-medium">{{ $menu->name }}</td>
        <td class="px-4 py-3">@if($menu->location)<x-badge color="indigo">{{ $menu->location }}</x-badge>@else<span class="text-slate-400">—</span>@endif</td>
        <td class="px-4 py-3">{{ $menu->items_count }}</td>
        <td class="px-4 py-3 text-right whitespace-nowrap">
            @can('menus.edit')<x-btn size="sm" variant="ghost" :href="route('admin.menus.edit', $menu)">Edit</x-btn>@endcan
            @can('menus.delete')<form method="POST" action="{{ route('admin.menus.destroy', $menu) }}" class="inline" onsubmit="return confirm('Delete menu?')">@csrf @method('DELETE')<x-btn size="sm" variant="ghost" type="submit" class="!text-red-600">Delete</x-btn></form>@endcan
        </td>
    </tr>
    @empty<tr><td colspan="4" class="px-4 py-10 text-center text-slate-400">No menus yet.</td></tr>@endforelse
</x-table>
@endsection
