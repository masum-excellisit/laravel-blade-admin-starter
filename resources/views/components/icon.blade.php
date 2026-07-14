@props(['name', 'class' => 'w-5 h-5'])
@php
$paths = [
    'edit' => 'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.4-9.6a2 2 0 112.8 2.8L11.8 15.2 8 16l.8-3.8 9.8-9.8z',
    'trash' => 'M19 7l-.9 12.1a2 2 0 01-2 1.9H7.9a2 2 0 01-2-1.9L5 7m5 4v6m4-6v6M9 7V4a1 1 0 011-1h4a1 1 0 011 1v3M4 7h16',
    'eye' => 'M2.5 12S5.5 5.5 12 5.5 21.5 12 21.5 12 18.5 18.5 12 18.5 2.5 12 2.5 12zM12 15a3 3 0 100-6 3 3 0 000 6z',
    'plus' => 'M12 5v14M5 12h14',
    'search' => 'M21 21l-4.3-4.3M11 19a8 8 0 100-16 8 8 0 000 16z',
    'reply' => 'M9 17l-5-5 5-5M4 12h11a5 5 0 015 5v1',
    'copy' => 'M8 8V5a2 2 0 012-2h9a2 2 0 012 2v9a2 2 0 01-2 2h-3M5 8h9a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2v-9a2 2 0 012-2z',
    'mail' => 'M3 8l9 6 9-6M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z',
    'external' => 'M14 3h7v7m0-7L10 14M19 14v5a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h5',
    'x' => 'M6 6l12 12M18 6L6 18',
    'chevron-left' => 'M15 19l-7-7 7-7',
    'chevron-right' => 'M9 5l7 7-7 7',
    'upload' => 'M12 16V4m0 0L8 8m4-4l4 4M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2',
    'filter' => 'M4 5h16M7 12h10M10 19h4',
    'check' => 'M5 13l4 4L19 7',
];
@endphp
<svg {{ $attributes->merge(['class' => $class]) }} fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $paths[$name] ?? '' }}"/></svg>
