@extends('layouts.admin')
@section('title', 'Edit menu')
@section('content')
<x-page-header title="Edit menu: {{ $menu->name }}" />
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">
        <x-card title="Menu items">
            <div class="space-y-2">
                @forelse($menu->rootItems as $item)
                <div class="flex items-center justify-between px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-700">
                    <div>
                        <p class="font-medium">{{ $item->label }}</p>
                        <p class="text-xs text-slate-400">{{ $item->type }}: {{ $item->value }}</p>
                    </div>
                    <form method="POST" action="{{ route('admin.menus.items.destroy', $item) }}" onsubmit="return confirm('Remove?')">@csrf @method('DELETE')<x-btn size="sm" variant="ghost" type="submit" class="!text-red-600">Remove</x-btn></form>
                </div>
                @empty<p class="text-sm text-slate-400">No items yet — add one on the right.</p>@endforelse
            </div>
        </x-card>
    </div>
    <div class="space-y-6">
        <x-card title="Settings">
            <form method="POST" action="{{ route('admin.menus.update', $menu) }}" class="space-y-4">@csrf @method('PUT')
                <x-form.input name="name" label="Name" :value="$menu->name" required />
                <x-form.input name="location" label="Location" :value="$menu->location" />
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
                <div x-show="type==='url'"><x-form.input name="value" label="URL" placeholder="https://…" x-bind:required="type==='url'" /></div>
                <div x-show="type==='page'" x-cloak><x-form.select name="value" label="Page slug" :options="$pages" placeholder="Select page" /></div>
                <div x-show="type==='route'" x-cloak><x-form.input name="value" label="Route name" placeholder="home / blog.index" /></div>
                <x-btn type="submit" class="w-full">Add item</x-btn>
            </form>
        </x-card>
    </div>
</div>
@endsection
