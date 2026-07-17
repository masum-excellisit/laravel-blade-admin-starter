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
            'tone' => 'view',
            'path' => 'M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z',
        ],
        'create' => [
            'label' => 'Create',
            'tone' => 'create',
            'path' => 'M12 4v16m8-8H4',
        ],
        'edit' => [
            'label' => 'Edit',
            'tone' => 'edit',
            'path' => 'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z',
        ],
        'delete' => [
            'label' => 'Delete',
            'tone' => 'delete',
            'path' => 'M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16',
        ],
    ];
@endphp

<div
    class="perm-modules"
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
    <div class="perm-modules__toolbar">
        <div class="perm-modules__stats">
            <div class="perm-stat">
                <span class="perm-stat__icon perm-stat__icon--total">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </span>
                <div>
                    <p class="perm-stat__value">{{ $totalPermissions }}</p>
                    <p class="perm-stat__label">Total permissions</p>
                </div>
            </div>
            <div class="perm-stat">
                <span class="perm-stat__icon perm-stat__icon--modules">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                </span>
                <div>
                    <p class="perm-stat__value">{{ $totalModules }}</p>
                    <p class="perm-stat__label">Modules</p>
                </div>
            </div>
        </div>

        @if($searchable)
            <div class="perm-modules__search">
                <svg class="perm-modules__search-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="search" x-model="query" placeholder="Filter modules or permissions…" class="perm-modules__search-input">
            </div>
        @endif
    </div>

    @if(empty($modules))
        <div class="perm-modules__empty">
            No permissions found.
        </div>
    @else
        <div class="perm-modules__grid">
            @foreach($modules as $module => $permissions)
                @php
                    $moduleLabel = str($module)->replace(['-', '_'], ' ')->title();
                    $moduleInitial = strtoupper(substr($moduleLabel, 0, 1));
                    $permissionNames = collect($permissions)->pluck('name')->map(fn ($n) => (string) $n)->values()->all();
                    $searchBlob = strtolower($module.' '.$moduleLabel.' '.implode(' ', $permissionNames));
                @endphp

                <div
                    class="perm-card"
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
                        <div class="perm-card__header">
                            <span class="perm-card__avatar">{{ $moduleInitial }}</span>
                            <div class="perm-card__title-wrap">
                                <h3 class="perm-card__title">{{ $moduleLabel }}</h3>
                            </div>

                            @if($selectable)
                                <label class="perm-card__all">
                                    <input
                                        type="checkbox"
                                        class="perm-card__checkbox"
                                        :checked="allSelected"
                                        :indeterminate.prop="someSelected"
                                        @change="toggleAll()"
                                    >
                                    All
                                </label>
                            @endif

                            <span class="perm-card__count">
                                @if($selectable)
                                    <span x-text="selected.length"></span>/{{ count($permissions) }}
                                @else
                                    {{ count($permissions) }}
                                @endif
                            </span>
                        </div>

                        <ul class="perm-card__list">
                            @foreach($permissions as $permission)
                                @php
                                    $parts = explode('.', $permission->name);
                                    $action = strtolower($parts[1] ?? $permission->name);
                                    $meta = $actionMeta[$action] ?? [
                                        'label' => str($action)->replace(['-', '_'], ' ')->title()->toString(),
                                        'tone' => 'default',
                                        'path' => 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
                                    ];
                                @endphp
                                <li class="perm-row">
                                    @if($selectable)
                                        <input
                                            type="checkbox"
                                            name="{{ $name }}[]"
                                            value="{{ $permission->name }}"
                                            class="perm-card__checkbox"
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

                                    <span class="perm-row__icon perm-row__icon--{{ $meta['tone'] }}">
                                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $meta['path'] }}"/></svg>
                                    </span>

                                    <div class="perm-row__text">
                                        <p class="perm-row__label">{{ $meta['label'] }}</p>
                                        <p class="perm-row__slug">{{ $permission->name }}</p>
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
