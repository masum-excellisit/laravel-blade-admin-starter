@extends('layouts.admin')
@section('title', 'Edit menu')
@section('content')
@php
    $locations = ['header' => 'Header', 'footer' => 'Footer'];
@endphp
<x-page-header title="Edit menu: {{ $menu->name }}" subtitle="Drag items to reorder. Location is fixed to header or footer.">
    <x-slot:actions>
        <x-btn variant="outline" :href="route('admin.menus.index')">Back to menus</x-btn>
    </x-slot:actions>
</x-page-header>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">
        <x-card title="Menu items">
            <p class="text-sm text-slate-500 mb-4">Drag the handle to change order. Changes save automatically.</p>
            <div x-data="sortableMenu(@js(route('admin.menus.reorder', $menu)))">
                <div x-ref="list" class="space-y-2">
                    @forelse($menu->rootItems as $item)
                    <div data-item-id="{{ $item->id }}"
                         class="flex items-center gap-3 px-3 py-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800/60 shadow-sm">
                        <button type="button" data-drag-handle
                                class="cursor-grab active:cursor-grabbing p-1.5 rounded-lg text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700"
                                title="Drag to reorder" aria-label="Drag to reorder">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M7 4a1 1 0 11-2 0 1 1 0 012 0zm0 6a1 1 0 11-2 0 1 1 0 012 0zm0 6a1 1 0 11-2 0 1 1 0 012 0zm8-12a1 1 0 11-2 0 1 1 0 012 0zm0 6a1 1 0 11-2 0 1 1 0 012 0zm0 6a1 1 0 11-2 0 1 1 0 012 0z"/></svg>
                        </button>
                        <div class="flex-1 min-w-0">
                            <p class="font-medium truncate">{{ $item->label }}</p>
                            <p class="text-xs text-slate-400 truncate">{{ $item->type }} · {{ $item->value }}</p>
                            @if($item->children->count())
                                <ul class="mt-2 space-y-1 pl-3 border-l border-slate-200 dark:border-slate-600">
                                    @foreach($item->children as $child)
                                        <li class="text-xs text-slate-500">{{ $child->label }} <span class="text-slate-400">({{ $child->type }})</span></li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                        <form method="POST" action="{{ route('admin.menus.items.destroy', $item) }}" onsubmit="return confirm('Remove this item?')">
                            @csrf @method('DELETE')
                            <x-btn size="sm" variant="ghost" type="submit" class="!text-red-600">Remove</x-btn>
                        </form>
                    </div>
                    @empty
                    <p class="text-sm text-slate-400 py-6 text-center">No items yet — add one on the right.</p>
                    @endforelse
                </div>
            </div>
        </x-card>
    </div>
    <div class="space-y-6">
        <x-card title="Settings">
            <form method="POST" action="{{ route('admin.menus.update', $menu) }}" class="space-y-4">@csrf @method('PUT')
                <x-form.input name="name" label="Name" :value="$menu->name" required />
                <x-form.select name="location" label="Location" :options="$locations" :selected="$menu->location" placeholder="Select location" required />
                <p class="text-xs text-slate-400 -mt-2">Fixed locations used by the public site: header and footer.</p>
                <x-btn type="submit" class="w-full">Save</x-btn>
            </form>
        </x-card>
        <x-card title="Add item">
            <form method="POST" action="{{ route('admin.menus.items.store', $menu) }}" class="space-y-4" x-data="{ type:'url' }">@csrf
                <x-form.input name="label" label="Label" required />
                <div>
                    <x-form.label>Link type</x-form.label>
                    <select name="type" x-model="type" class="w-full rounded-xl border border-slate-300 dark:border-slate-600 dark:bg-slate-900/50 text-sm px-3.5 py-2.5">
                        <option value="url">Custom URL</option>
                        <option value="page">Page</option>
                        <option value="route">Named route</option>
                    </select>
                </div>
                <div x-show="type==='url'"><x-form.input name="value" label="URL" placeholder="https://…" /></div>
                <div x-show="type==='page'" x-cloak><x-form.select name="value" label="Page slug" :options="$pages" placeholder="Select page" /></div>
                <div x-show="type==='route'" x-cloak><x-form.input name="value" label="Route name" placeholder="home / blog.index / services.index" /></div>
                @if($menu->rootItems->count())
                <x-form.select name="parent_id" label="Parent (optional)" :options="$menu->rootItems->pluck('label', 'id')" placeholder="Top level" />
                @endif
                <x-btn type="submit" class="w-full">Add item</x-btn>
            </form>
        </x-card>
    </div>
</div>
@endsection
