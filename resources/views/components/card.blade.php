@props(['title' => null, 'padded' => true])
<div {{ $attributes->merge(['class' => 'bg-white dark:bg-slate-800/60 rounded-2xl border border-slate-200/70 dark:border-slate-700/60 shadow-sm']) }}>
    @if($title)
    <div class="px-6 py-4 border-b border-slate-200/70 dark:border-slate-700/60">
        <h3 class="font-semibold text-slate-800 dark:text-slate-100">{{ $title }}</h3>
    </div>
    @endif
    <div class="{{ $padded ? 'p-6' : '' }}">{{ $slot }}</div>
</div>
