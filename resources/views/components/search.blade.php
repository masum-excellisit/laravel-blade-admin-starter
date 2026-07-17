@props(['placeholder' => 'Search…', 'action' => null])
@php
    $hasActive = filled(request('search')) || filled(request('status'));
@endphp
<form
    method="GET"
    action="{{ $action }}"
    class="flex flex-wrap items-center gap-3 mb-5"
    x-data="{
        q: @js((string) request('search', '')),
        timer: null,
        submitLive() {
            clearTimeout(this.timer);
            this.timer = setTimeout(() => { this.$el.submit() }, 350);
        },
        focusEnd() {
            this.$nextTick(() => {
                const input = this.$refs.search;
                if (!input) return;
                const len = input.value.length;
                input.focus();
                input.setSelectionRange(len, len);
            });
        }
    }"
    x-init="if (q) focusEnd()"
>
    {{-- Preserve sort/direction/etc; drop page so a new search starts at page 1. --}}
    @foreach(request()->except(['search', 'status', 'page']) as $key => $value)
        @if(is_scalar($value))
            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
        @endif
    @endforeach

    <div class="relative flex-1 min-w-[15rem]">
        <x-icon name="search" class="w-4.5 h-4.5 absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400" />
        <input
            x-ref="search"
            name="search"
            x-model="q"
            @input="submitLive()"
            @keydown.enter.prevent="clearTimeout(timer); $el.form.submit()"
            placeholder="{{ $placeholder }}"
            autocomplete="off"
            class="w-full pl-11 pr-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm brand-ring shadow-sm"
        >
    </div>
    {{ $filters ?? '' }}
    @if($hasActive)
        <x-btn variant="ghost" :href="url()->current()">Clear</x-btn>
    @endif
</form>
