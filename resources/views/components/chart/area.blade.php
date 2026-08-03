{{--
    Dependency-free multi-series area chart (inline SVG + Alpine hover).

    <x-chart.area :labels="['Mon','Tue']" :series="[['name'=>'Posts','color'=>'#6366f1','data'=>[1,4]]]" />

    Remove: delete this file and any <x-chart.area> usage.
--}}
@props([
    'labels' => [],
    'series' => [],
    'height' => 260,
    'gridLines' => 4,
    'suffix' => '',
])
@php
    $uid = 'ca'.substr(md5(uniqid('', true)), 0, 8);
    $count = count($labels);
    $max = 0;
    foreach ($series as $s) {
        $max = max($max, ...(count($s['data']) ? $s['data'] : [0]));
    }
    // Pick a step that divides the axis evenly, so every gridline is a round number.
    $nice = function (float $v) {
        if ($v <= 1) return 1;
        $exp = pow(10, floor(log10($v)));
        foreach ([1, 2, 2.5, 5, 10] as $m) {
            if ($v <= $m * $exp) return (int) ceil($m * $exp);
        }
        return (int) ($exp * 10);
    };
    $step = $nice(max($max, 1) / $gridLines);
    $top = $step * $gridLines;

    $VW = 1000; $VH = 300;
    $point = fn ($i, $v) => [
        $count > 1 ? ($i / ($count - 1)) * $VW : $VW / 2,
        $VH - ($v / $top) * $VH,
    ];

    // Catmull-Rom -> cubic bezier for a soft, premium curve.
    $curve = function (array $pts) {
        if (! $pts) return '';
        $d = 'M'.round($pts[0][0], 2).','.round($pts[0][1], 2);
        $n = count($pts);
        for ($i = 0; $i < $n - 1; $i++) {
            $p0 = $pts[max($i - 1, 0)];
            $p1 = $pts[$i];
            $p2 = $pts[$i + 1];
            $p3 = $pts[min($i + 2, $n - 1)];
            $c1 = [$p1[0] + ($p2[0] - $p0[0]) / 6, $p1[1] + ($p2[1] - $p0[1]) / 6];
            $c2 = [$p2[0] - ($p3[0] - $p1[0]) / 6, $p2[1] - ($p3[1] - $p1[1]) / 6];
            $d .= 'C'.round($c1[0], 2).','.round($c1[1], 2).' '.round($c2[0], 2).','.round($c2[1], 2).' '.round($p2[0], 2).','.round($p2[1], 2);
        }
        return $d;
    };

    $rendered = [];
    foreach ($series as $k => $s) {
        $pts = [];
        foreach ($s['data'] as $i => $v) { $pts[] = $point($i, $v); }
        $line = $curve($pts);
        $rendered[] = [
            'name' => $s['name'],
            'color' => $s['color'],
            'data' => $s['data'],
            'line' => $line,
            'fill' => $line ? $line.'L'.$VW.','.$VH.'L0,'.$VH.'Z' : '',
            'grad' => $uid.'g'.$k,
            'dots' => array_map(fn ($p) => [
                'x' => round($count > 1 ? $p[0] / $VW * 100 : 50, 4),
                'y' => round($p[1] / $VH * 100, 4),
            ], $pts),
        ];
    }

    $ticks = [];
    for ($i = $gridLines; $i >= 0; $i--) { $ticks[] = $step * $i; }
@endphp

<div class="ez-chart" x-data="{
        i: null,
        n: {{ max($count, 1) }},
        labels: @js($labels),
        series: @js(array_map(fn ($s) => ['name' => $s['name'], 'color' => $s['color'], 'data' => $s['data'], 'dots' => $s['dots']], $rendered)),
        track(e) {
            const r = $refs.plot.getBoundingClientRect();
            if (this.n < 2) { this.i = 0; return; }
            const p = Math.min(Math.max((e.clientX - r.left) / r.width, 0), 1);
            this.i = Math.round(p * (this.n - 1));
        },
    }">
    <div class="ez-chart__body">
        <div class="ez-chart__axis" aria-hidden="true">
            @foreach($ticks as $t)
                <span>{{ $t >= 1000 ? round($t / 1000, 1).'k' : $t }}{{ $suffix }}</span>
            @endforeach
        </div>

        <div class="ez-chart__plot" x-ref="plot" style="height: {{ $height }}px"
             @mousemove="track($event)" @mouseleave="i = null"
             @touchstart.passive="track($event.touches[0])" @touchmove.passive="track($event.touches[0])">

            <div class="ez-chart__grid" aria-hidden="true">
                @foreach($ticks as $t)<i></i>@endforeach
            </div>

            <svg class="ez-chart__svg" viewBox="0 0 {{ $VW }} {{ $VH }}" preserveAspectRatio="none" role="img"
                 aria-label="{{ collect($series)->pluck('name')->join(', ') }} over time">
                <defs>
                    @foreach($rendered as $s)
                        <linearGradient id="{{ $s['grad'] }}" x1="0" y1="0" x2="0" y2="1">
                            <stop offset="0%" stop-color="{{ $s['color'] }}" stop-opacity="0.34"/>
                            <stop offset="60%" stop-color="{{ $s['color'] }}" stop-opacity="0.08"/>
                            <stop offset="100%" stop-color="{{ $s['color'] }}" stop-opacity="0"/>
                        </linearGradient>
                    @endforeach
                </defs>
                @foreach($rendered as $s)
                    @if($s['fill'])<path d="{{ $s['fill'] }}" fill="url(#{{ $s['grad'] }})"/>@endif
                    <path class="ez-chart__line" d="{{ $s['line'] }}" fill="none" stroke="{{ $s['color'] }}"
                          stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" vector-effect="non-scaling-stroke"/>
                @endforeach
            </svg>

            <div class="ez-chart__cursor" x-cloak x-show="i !== null"
                 :style="`left: ${n > 1 ? (i / (n - 1)) * 100 : 50}%`">
                <template x-for="(s, k) in series" :key="k">
                    <span class="ez-chart__dot" :style="`top: ${s.dots[i]?.y ?? 0}%; --dot: ${s.color}`"></span>
                </template>
            </div>

            <div class="ez-chart__tip" x-cloak x-show="i !== null" x-transition.opacity.duration.120ms
                 :class="{ 'is-right': n > 1 && i / (n - 1) > 0.6 }"
                 :style="`left: ${n > 1 ? (i / (n - 1)) * 100 : 50}%`">
                <p class="ez-chart__tip-label" x-text="labels[i]"></p>
                <template x-for="(s, k) in series" :key="k">
                    <p class="ez-chart__tip-row">
                        <i :style="`background: ${s.color}`"></i>
                        <span x-text="s.name"></span>
                        <b x-text="s.data[i]"></b>
                    </p>
                </template>
            </div>
        </div>
    </div>

    @php $every = max(1, (int) ceil($count / 7)); @endphp
    <div class="ez-chart__labels">
        @foreach($labels as $i => $l)
            <span @class(['is-edge' => $i % $every === 0 || $i === $count - 1])>{{ $l }}</span>
        @endforeach
    </div>

    <div class="ez-chart__legend">
        @foreach($rendered as $s)
            <span><i style="background: {{ $s['color'] }}"></i>{{ $s['name'] }}</span>
        @endforeach
    </div>
</div>
