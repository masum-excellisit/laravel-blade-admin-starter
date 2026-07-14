@props(['variant' => 'primary', 'href' => null, 'type' => 'button', 'size' => 'md'])
@php
$base = 'inline-flex items-center justify-center gap-2 font-semibold rounded-xl transition focus:outline-none disabled:opacity-50 disabled:cursor-not-allowed';
$sizes = ['sm' => 'px-3 py-1.5 text-sm', 'md' => 'px-4 py-2.5 text-sm', 'lg' => 'px-6 py-3 text-base'];
$variants = [
    'primary' => 'text-white brand-gradient shadow-lg shadow-primary/25 hover:brightness-110',
    'secondary' => 'bg-slate-100 text-slate-700 hover:bg-slate-200 dark:bg-slate-700 dark:text-slate-100',
    'outline' => 'border border-slate-300 text-slate-700 hover:bg-slate-50 dark:border-slate-600 dark:text-slate-200 dark:hover:bg-slate-800',
    'danger' => 'bg-red-600 text-white hover:bg-red-700',
    'ghost' => 'text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800',
];
$classes = $base.' '.$sizes[$size].' '.($variants[$variant] ?? $variants['primary']);
@endphp
@if($href)
<a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</a>
@else
<button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</button>
@endif
