@extends('layouts.admin')
@section('title', 'Edit menu')
@section('content')
@php
    $editableItems = $menu->items->mapWithKeys(fn ($item) => [
        $item->id => [
            'id' => $item->id,
            'label' => $item->label,
            'type' => $item->type,
            'value' => $item->value,
            'parent_id' => $item->parent_id,
            'has_children' => $menu->items->where('parent_id', $item->id)->isNotEmpty(),
            'update_url' => route('admin.menus.items.update', $item),
        ],
    ]);
@endphp
<x-page-header title="Edit menu: {{ $menu->name }}" subtitle="Drag items to reorder. Click Edit to change an item.">
    <x-slot:actions>
        <x-btn variant="outline" :href="route('admin.menus.index')">Back to menus</x-btn>
    </x-slot:actions>
</x-page-header>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6"
     x-data="{
        mode: 'create',
        type: 'url',
        label: '',
        value: '',
        parentId: '',
        updateUrl: '',
        hasChildren: false,
        storeUrl: @js(route('admin.menus.items.store', $menu)),
        items: @js($editableItems),
        startEditById(id) {
            const item = this.items[id] || this.items[String(id)];
            if (!item) return;
            this.mode = 'edit';
            this.type = item.type;
            this.label = item.label;
            this.value = item.value ?? '';
            this.parentId = item.parent_id ? String(item.parent_id) : '';
            this.updateUrl = item.update_url;
            this.hasChildren = !!item.has_children;
            this.$nextTick(() => this.$refs.itemForm?.scrollIntoView({ behavior: 'smooth', block: 'nearest' }));
        },
        cancelEdit() {
            this.mode = 'create';
            this.type = 'url';
            this.label = '';
            this.value = '';
            this.parentId = '';
            this.updateUrl = '';
            this.hasChildren = false;
        }
     }"
     @menu-edit="startEditById($event.detail)">
    <div class="lg:col-span-2 space-y-6">
        <x-card title="Menu items">
            <p class="text-sm text-slate-500 mb-4">Drag the handle to change order. Changes save automatically.</p>
            <div x-data="sortableMenu(@js(route('admin.menus.reorder', $menu)))">
                <div x-ref="list" class="space-y-2">
                    @forelse($menu->rootItems as $item)
                    <div data-item-id="{{ $item->id }}"
                         class="flex items-start gap-3 px-3 py-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800/60 shadow-sm">
                        <button type="button" data-drag-handle
                                class="cursor-grab active:cursor-grabbing p-1.5 mt-0.5 rounded-lg text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700"
                                title="Drag to reorder" aria-label="Drag to reorder">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M7 4a1 1 0 11-2 0 1 1 0 012 0zm0 6a1 1 0 11-2 0 1 1 0 012 0zm0 6a1 1 0 11-2 0 1 1 0 012 0zm8-12a1 1 0 11-2 0 1 1 0 012 0zm0 6a1 1 0 11-2 0 1 1 0 012 0zm0 6a1 1 0 11-2 0 1 1 0 012 0z"/></svg>
                        </button>
                        <div class="flex-1 min-w-0">
                            <p class="font-medium truncate">{{ $item->label }}</p>
                            <p class="text-xs text-slate-400 truncate">{{ $item->type }} · {{ $item->value }}</p>
                            @if($item->children->count())
                                <ul class="mt-2 space-y-2 pl-3 border-l border-slate-200 dark:border-slate-600">
                                    @foreach($item->children as $child)
                                        <li class="flex items-center justify-between gap-2">
                                            <span class="text-xs text-slate-500 truncate">{{ $child->label }} <span class="text-slate-400">({{ $child->type }} · {{ $child->value }})</span></span>
                                            <div class="flex items-center gap-1 shrink-0">
                                                @can('menus.edit')
                                                <button type="button"
                                                        class="text-xs text-primary hover:underline px-1"
                                                        x-on:click="$dispatch('menu-edit', {{ (int) $child->id }})">Edit</button>
                                                <form method="POST" action="{{ route('admin.menus.items.destroy', $child) }}" onsubmit="return confirm('Remove this item?')">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="text-xs text-red-600 hover:underline px-1">Remove</button>
                                                </form>
                                                @endcan
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                        <div class="flex items-center gap-1 shrink-0">
                            @can('menus.edit')
                            <button type="button"
                                    class="inline-flex items-center justify-center gap-2 font-semibold rounded-xl transition px-3 py-1.5 text-sm text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800"
                                    x-on:click="$dispatch('menu-edit', {{ (int) $item->id }})">Edit</button>
                            <form method="POST" action="{{ route('admin.menus.items.destroy', $item) }}" onsubmit="return confirm('Remove this item?')">
                                @csrf @method('DELETE')
                                <x-btn size="sm" variant="ghost" type="submit" class="!text-red-600">Remove</x-btn>
                            </form>
                            @endcan
                        </div>
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
        <div x-ref="itemForm" class="bg-white dark:bg-slate-800/60 rounded-2xl border border-slate-200/70 dark:border-slate-700/60 shadow-sm">
            <div class="px-6 py-4 border-b border-slate-200/70 dark:border-slate-700/60">
                <h3 class="font-semibold text-slate-800 dark:text-slate-100" x-text="mode === 'edit' ? 'Edit item' : 'Add item'"></h3>
            </div>
            <div class="p-6">
                <form method="POST" class="space-y-4" :action="mode === 'edit' ? updateUrl : storeUrl">
                    @csrf
                    <template x-if="mode === 'edit'">
                        <div><input type="hidden" name="_method" value="PUT"></div>
                    </template>
                    <div>
                        <x-form.label for="item_label" required>Label</x-form.label>
                        <input id="item_label" type="text" name="label" x-model="label" required
                               class="w-full rounded-xl border-slate-300 dark:border-slate-600 dark:bg-slate-900/50 dark:text-white shadow-sm brand-ring focus:border-primary text-sm px-3.5 py-2.5 border">
                    </div>
                    <div>
                        <x-form.label>Link type</x-form.label>
                        <select name="type" x-model="type" class="w-full rounded-xl border border-slate-300 dark:border-slate-600 dark:bg-slate-900/50 text-sm px-3.5 py-2.5">
                            <option value="url">Custom URL</option>
                            <option value="page">Page</option>
                            <option value="route">Named route</option>
                        </select>
                    </div>
                    <template x-if="type === 'url'">
                        <div>
                            <x-form.label for="item_value_url" required>URL</x-form.label>
                            <input id="item_value_url" type="text" name="value" x-model="value" required placeholder="https://…"
                                   class="w-full rounded-xl border-slate-300 dark:border-slate-600 dark:bg-slate-900/50 dark:text-white shadow-sm brand-ring focus:border-primary text-sm px-3.5 py-2.5 border">
                        </div>
                    </template>
                    <template x-if="type === 'page'">
                        <div>
                            <x-form.label for="item_value_page" required>Page</x-form.label>
                            <select id="item_value_page" name="value" x-model="value" required
                                    class="w-full rounded-xl border border-slate-300 dark:border-slate-600 dark:bg-slate-900/50 dark:text-white shadow-sm brand-ring focus:border-primary text-sm px-3.5 py-2.5">
                                <option value="">Select page</option>
                                @foreach($pages as $slug => $title)
                                    <option value="{{ $slug }}">{{ $title }}</option>
                                @endforeach
                            </select>
                        </div>
                    </template>
                    <template x-if="type === 'route'">
                        <div>
                            <x-form.label for="item_value_route" required>Route name</x-form.label>
                            <input id="item_value_route" type="text" name="value" x-model="value" required placeholder="home / blog.index / services.index"
                                   class="w-full rounded-xl border-slate-300 dark:border-slate-600 dark:bg-slate-900/50 dark:text-white shadow-sm brand-ring focus:border-primary text-sm px-3.5 py-2.5 border">
                        </div>
                    </template>
                    @if($menu->rootItems->count())
                    <template x-if="!hasChildren">
                        <div>
                            <x-form.label for="item_parent">Parent (optional)</x-form.label>
                            <select id="item_parent" name="parent_id" x-model="parentId"
                                    class="w-full rounded-xl border border-slate-300 dark:border-slate-600 dark:bg-slate-900/50 dark:text-white shadow-sm brand-ring focus:border-primary text-sm px-3.5 py-2.5">
                                <option value="">Top level</option>
                                @foreach($menu->rootItems as $root)
                                    <option value="{{ $root->id }}">{{ $root->label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </template>
                    @endif
                    <div class="flex gap-2">
                        <x-btn type="submit" class="flex-1">
                            <span x-text="mode === 'edit' ? 'Update item' : 'Add item'"></span>
                        </x-btn>
                        <x-btn type="button" variant="outline" x-show="mode === 'edit'" x-cloak x-on:click="cancelEdit()">Cancel</x-btn>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
