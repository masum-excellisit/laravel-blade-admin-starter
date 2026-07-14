@props(['color' => 'slate'])
@php
$map = [
    'slate' => 'bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-200',
    'green' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300',
    'red' => 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300',
    'amber' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300',
    'indigo' => 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300',
];
@endphp
<span {{ $attributes->merge(['class' => 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium '.($map[$color] ?? $map['slate'])]) }}>{{ $slot }}</span>
