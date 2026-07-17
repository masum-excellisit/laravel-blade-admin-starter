@props([
    'columns' => [],
    'headings' => [],
    'bulk' => false,
    'sortable' => [],
])

@php
    if (count($columns) === 0 && count($headings)) {
        $columns = collect($headings)->map(fn ($h, $i) => [
            'key' => is_array($sortable) && ! empty($sortable[$i]) ? $sortable[$i] : null,
            'label' => $h,
            'sortable' => is_array($sortable) && ! empty($sortable[$i]),
        ])->all();
    }

    $currentSort = request('sort');
    $currentDir = request('direction', 'desc');
@endphp

<div @if($bulk) x-data="bulkTable()" @endif>
    @if($bulk && isset($toolbar))
        {{ $toolbar }}
    @endif

    <div class="overflow-x-auto rounded-2xl border border-slate-200/70 dark:border-slate-700/60">
        <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700 text-sm">
            @if(count($columns))
            <thead class="bg-slate-50 dark:bg-slate-800/80">
                <tr>
                    @if($bulk)
                    <th class="px-4 py-3 w-10">
                        <input type="checkbox" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
                               x-ref="selectAll" x-on:change="toggleAll($event.target.checked)">
                    </th>
                    @endif
                    @foreach($columns as $col)
                        @php
                            $key = is_array($col) ? ($col['key'] ?? null) : null;
                            $label = is_array($col) ? ($col['label'] ?? '') : $col;
                            $isSortable = is_array($col) && ! empty($col['sortable']) && $key;
                            $isActive = $isSortable && $currentSort === $key;
                            $nextDir = ($isActive && $currentDir === 'asc') ? 'desc' : 'asc';
                        @endphp
                        <th class="px-4 py-3 text-left font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider text-xs whitespace-nowrap">
                            @if($isSortable)
                                <a href="{{ request()->fullUrlWithQuery(['sort' => $key, 'direction' => $nextDir, 'page' => null]) }}"
                                   class="inline-flex items-center gap-1 hover:text-slate-800 dark:hover:text-slate-200 transition group">
                                    <span>{{ $label }}</span>
                                    <span class="inline-flex flex-col leading-none text-[9px] opacity-40 group-hover:opacity-80 {{ $isActive ? '!opacity-100 text-indigo-500' : '' }}">
                                        <svg class="w-3 h-3 {{ $isActive && $currentDir === 'asc' ? 'text-indigo-600' : '' }}" viewBox="0 0 12 12" fill="currentColor"><path d="M6 3L9.5 7h-7L6 3z"/></svg>
                                        <svg class="w-3 h-3 -mt-1 {{ $isActive && $currentDir === 'desc' ? 'text-indigo-600' : '' }}" viewBox="0 0 12 12" fill="currentColor"><path d="M6 9L2.5 5h-7L6 9z"/></svg>
                                    </span>
                                </a>
                            @else
                                {{ $label }}
                            @endif
                        </th>
                    @endforeach
                </tr>
            </thead>
            @endif
            <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60 bg-white dark:bg-slate-800/40">
                {{ $slot }}
            </tbody>
        </table>
    </div>
</div>
