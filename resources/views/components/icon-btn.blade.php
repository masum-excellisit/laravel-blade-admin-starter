@props(['icon', 'href' => null, 'variant' => 'default', 'type' => 'button', 'label' => null])
@php
$variants = [
    'default' => 'text-slate-500 hover:text-primary hover:bg-primary-soft dark:text-slate-400 dark:hover:bg-slate-700',
    'danger' => 'text-slate-500 hover:text-red-600 hover:bg-red-50 dark:text-slate-400 dark:hover:bg-red-900/30',
    'view' => 'text-slate-500 hover:text-emerald-600 hover:bg-emerald-50 dark:text-slate-400 dark:hover:bg-emerald-900/30',
];
$cls = 'inline-flex items-center justify-center h-9 w-9 rounded-lg transition '.($variants[$variant] ?? $variants['default']);
@endphp
@if($href)
<a href="{{ $href }}" @if($label) title="{{ $label }}" @endif {{ $attributes->merge(['class' => $cls]) }}><x-icon :name="$icon" class="w-4.5 h-4.5" /></a>
@else
<button type="{{ $type }}" @if($label) title="{{ $label }}" @endif {{ $attributes->merge(['class' => $cls]) }}><x-icon :name="$icon" class="w-4.5 h-4.5" /></button>
@endif
