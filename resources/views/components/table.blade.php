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
                                   class="inline-flex items-center gap-1.5 hover:text-slate-800 dark:hover:text-slate-200 transition"
                                   title="Sort by {{ $label }}">
                                    <span>{{ $label }}</span>
                                    @if($isActive && $currentDir === 'asc')
                                        <svg class="table-sort-icon table-sort-icon--active" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                            <path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd"/>
                                        </svg>
                                    @elseif($isActive)
                                        <svg class="table-sort-icon table-sort-icon--active" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                        </svg>
                                    @else
                                        <svg class="table-sort-icon table-sort-icon--idle" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                                            <path d="M6 8l4-4 4 4M6 12l4 4 4-4" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    @endif
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
