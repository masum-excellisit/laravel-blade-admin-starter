@props(['placeholder' => 'Search…', 'action' => null])
<form method="GET" action="{{ $action }}" class="flex flex-wrap items-center gap-3 mb-5">
    <div class="relative flex-1 min-w-[15rem]">
        <x-icon name="search" class="w-4.5 h-4.5 absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400" />
        <input name="search" value="{{ request('search') }}" placeholder="{{ $placeholder }}"
               class="w-full pl-11 pr-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm brand-ring shadow-sm">
    </div>
    {{ $filters ?? '' }}
    <x-btn type="submit" variant="secondary"><x-icon name="filter" class="w-4 h-4" /> Filter</x-btn>
    @if(request()->hasAny(['search']) || request('status'))
        <x-btn variant="ghost" :href="url()->current()">Clear</x-btn>
    @endif
</form>
