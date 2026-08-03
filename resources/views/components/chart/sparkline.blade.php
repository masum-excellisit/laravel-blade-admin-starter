{{--
    Dependency-free sparkline for KPI cards.

    <x-chart.sparkline :data="[1,3,2,6]" color="#6366f1" />

    Remove: delete this file and any <x-chart.sparkline> usage.
--}}
@props([
    'data' => [],
    'color' => 'currentColor',
    'height' => 40,
    'fill' => true,
])
@php
    $uid = 'sp'.substr(md5(uniqid('', true)), 0, 8);
    $values = array_values(array_map('floatval', $data));
    $n = count($values);
    $max = $n ? max($values) : 0;
    $min = $n ? min($values) : 0;
    $span = ($max - $min) > 0 ? ($max - $min) : 1;
    $W = 200; $H = 60; $pad = 6;

    $pts = [];
    $inset = 2; // keeps the end caps inside the viewBox
    foreach ($values as $i => $v) {
        $pts[] = [
            $n > 1 ? $inset + ($i / ($n - 1)) * ($W - $inset * 2) : $W / 2,
            $H - $pad - (($v - $min) / $span) * ($H - $pad * 2),
        ];
    }

    $d = '';
    if ($pts) {
        $d = 'M'.round($pts[0][0], 2).','.round($pts[0][1], 2);
        for ($i = 0; $i < $n - 1; $i++) {
            $p0 = $pts[max($i - 1, 0)];
            $p1 = $pts[$i];
            $p2 = $pts[$i + 1];
            $p3 = $pts[min($i + 2, $n - 1)];
            $c1 = [$p1[0] + ($p2[0] - $p0[0]) / 6, $p1[1] + ($p2[1] - $p0[1]) / 6];
            $c2 = [$p2[0] - ($p3[0] - $p1[0]) / 6, $p2[1] - ($p3[1] - $p1[1]) / 6];
            $d .= 'C'.round($c1[0], 2).','.round($c1[1], 2).' '.round($c2[0], 2).','.round($c2[1], 2).' '.round($p2[0], 2).','.round($p2[1], 2);
        }
    }
@endphp

<svg {{ $attributes->merge(['class' => 'ez-spark']) }} viewBox="0 0 {{ $W }} {{ $H }}"
     preserveAspectRatio="none" style="height: {{ $height }}px" aria-hidden="true" focusable="false">
    @if($d)
        @if($fill)
            <defs>
                <linearGradient id="{{ $uid }}" x1="0" y1="0" x2="0" y2="1">
                    <stop offset="0%" stop-color="{{ $color }}" stop-opacity="0.45"/>
                    <stop offset="100%" stop-color="{{ $color }}" stop-opacity="0"/>
                </linearGradient>
            </defs>
            <path d="{{ $d }}L{{ $W }},{{ $H }}L0,{{ $H }}Z" fill="url(#{{ $uid }})"/>
        @endif
        <path class="ez-spark__line" d="{{ $d }}" fill="none" stroke="{{ $color }}" stroke-width="2"
              stroke-linecap="round" stroke-linejoin="round" vector-effect="non-scaling-stroke"/>
    @endif
</svg>
