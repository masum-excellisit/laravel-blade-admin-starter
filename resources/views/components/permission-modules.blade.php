@props([
    'modules' => [],
    'selectable' => false,
    'assigned' => [],
    'searchable' => false,
    'name' => 'permissions',
])

@php
    $assigned = collect($assigned)->map(fn ($v) => (string) $v)->all();
    $totalPermissions = collect($modules)->sum(fn ($permissions) => count($permissions));
    $totalModules = count($modules);

    $actionMeta = [
        'view' => [
            'label' => 'View',
            'color' => 'text-sky-600 bg-sky-50',
            'path' => 'M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z',
        ],
        'create' => [
            'label' => 'Create',
            'color' => 'text-emerald-600 bg-emerald-50',
            'path' => 'M12 4v16m8-8H4',
        ],
        'edit' => [
            'label' => 'Edit',
            'color' => 'text-amber-600 bg-amber-50',
            'path' => 'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z',
        ],
        'delete' => [
            'label' => 'Delete',
            'color' => 'text-rose-600 bg-rose-50',
            'path' => 'M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16',
        ],
    ];
@endphp

<div
    @if($searchable)
        x-data="{
            query: '',
            matches(text) {
                if (!this.query.trim()) return true;
                return text.toLowerCase().includes(this.query.trim().toLowerCase());
            }
        }"
    @endif
>
    <div class="mb-6 flex flex-wrap items-end justify-between gap-4">
        <div class="flex flex-wrap gap-3">
            <div class="inline-flex items-center gap-3 rounded-xl border border-slate-200 bg-white px-4 py-3 shadow-sm">
                <span class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-indigo-100 text-indigo-600">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </span>
                <div>
                    <p class="text-lg font-semibold text-slate-900">{{ $totalPermissions }}</p>
                    <p class="text-xs text-slate-500">Total permissions</p>
                </div>
            </div>
            <div class="inline-flex items-center gap-3 rounded-xl border border-slate-200 bg-white px-4 py-3 shadow-sm">
                <span class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-sky-100 text-sky-600">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                </span>
                <div>
                    <p class="text-lg font-semibold text-slate-900">{{ $totalModules }}</p>
                    <p class="text-xs text-slate-500">Modules</p>
                </div>
            </div>
        </div>

        @if($searchable)
            <div class="relative w-full max-w-xs">
                <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="search" x-model="query" placeholder="Filter modules or permissions…" class="w-full rounded-lg border-slate-300 py-2 pl-9 pr-3 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>
        @endif
    </div>

    @if(empty($modules))
        <div class="rounded-xl border border-dashed border-slate-300 bg-white px-6 py-12 text-center text-sm text-slate-500">
            No permissions found.
        </div>
    @else
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
            @foreach($modules as $module => $permissions)
                @php
                    $moduleLabel = str($module)->replace(['-', '_'], ' ')->title();
                    $moduleInitial = strtoupper(substr($moduleLabel, 0, 1));
                    $permissionNames = collect($permissions)->pluck('name')->map(fn ($n) => (string) $n)->values()->all();
                    $searchBlob = strtolower($module.' '.$moduleLabel.' '.implode(' ', $permissionNames));
                @endphp

                <div
                    class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm"
                    @if($searchable)
                        x-show="matches(@js($searchBlob))"
                        x-cloak
                    @endif
                >
                    <div
                        @if($selectable)
                            x-data="{
                                selected: @js(array_values(array_intersect($permissionNames, $assigned))),
                                allNames: @js($permissionNames),
                                get allSelected() { return this.allNames.length > 0 && this.selected.length === this.allNames.length },
                                get someSelected() { return this.selected.length > 0 && this.selected.length < this.allNames.length },
                                toggleAll() {
                                    this.selected = this.allSelected ? [] : [...this.allNames];
                                }
                            }"
                        @endif
                    >
                        <div class="flex items-center gap-3 border-b border-indigo-100 bg-indigo-50/80 px-4 py-3">
                            <span class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-indigo-600 text-sm font-semibold text-white">
                                {{ $moduleInitial }}
                            </span>
                            <div class="min-w-0 flex-1">
                                <h3 class="truncate text-sm font-semibold text-slate-900">{{ $moduleLabel }}</h3>
                            </div>

                            @if($selectable)
                                <label class="mr-1 inline-flex cursor-pointer items-center gap-1.5 text-xs text-slate-500">
                                    <input
                                        type="checkbox"
                                        class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
                                        :checked="allSelected"
                                        :indeterminate.prop="someSelected"
                                        @change="toggleAll()"
                                    >
                                    All
                                </label>
                            @endif

                            <span class="inline-flex h-6 min-w-6 items-center justify-center rounded-full bg-sky-100 px-2 text-xs font-semibold text-sky-700">
                                @if($selectable)
                                    <span x-text="selected.length"></span>/{{ count($permissions) }}
                                @else
                                    {{ count($permissions) }}
                                @endif
                            </span>
                        </div>

                        <ul class="divide-y divide-slate-100">
                            @foreach($permissions as $permission)
                                @php
                                    $parts = explode('.', $permission->name);
                                    $action = strtolower($parts[1] ?? $permission->name);
                                    $meta = $actionMeta[$action] ?? [
                                        'label' => str($action)->replace(['-', '_'], ' ')->title()->toString(),
                                        'color' => 'text-slate-600 bg-slate-100',
                                        'path' => 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
                                    ];
                                @endphp
                                <li class="flex items-center gap-3 px-4 py-3">
                                    @if($selectable)
                                        <input
                                            type="checkbox"
                                            name="{{ $name }}[]"
                                            value="{{ $permission->name }}"
                                            class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
                                            :checked="selected.includes(@js($permission->name))"
                                            @change="
                                                const key = @js($permission->name);
                                                if ($event.target.checked) {
                                                    if (!selected.includes(key)) selected.push(key);
                                                } else {
                                                    selected = selected.filter(v => v !== key);
                                                }
                                            "
                                        >
                                    @endif

                                    <span class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-lg {{ $meta['color'] }}">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $meta['path'] }}"/></svg>
                                    </span>

                                    <div class="min-w-0 flex-1">
                                        <p class="text-sm font-medium text-slate-800">{{ $meta['label'] }}</p>
                                        <p class="truncate font-mono text-xs text-slate-400">{{ $permission->name }}</p>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
