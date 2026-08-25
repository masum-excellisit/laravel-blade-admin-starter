{{--
    Dependency-free grouped bar chart (CSS bars + Alpine hover).

    <x-chart.bar :labels="$labels" :series="[['name'=>'Messages','color'=>'#6366f1','data'=>[...]]]" />

    Remove: delete this file and any <x-chart.bar> usage.
--}}
@props([
    'labels' => [],
    'series' => [],
    'height' => 220,
    'gridLines' => 4,
])
@php
    $max = 0;
    foreach ($series as $s) {
        $max = max($max, ...(count($s['data']) ? $s['data'] : [0]));
    }
    // Same "round numbers only" axis as <x-chart.area>.
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

    $ticks = [];
    for ($i = $gridLines; $i >= 0; $i--) { $ticks[] = $step * $i; }
@endphp

<div class="ez-chart ez-bars" x-data="{ i: null }">
    <div class="ez-chart__body">
        <div class="ez-chart__axis" aria-hidden="true">
            @foreach($ticks as $t)<span>{{ $t >= 1000 ? round($t / 1000, 1).'k' : $t }}</span>@endforeach
        </div>

        <div class="ez-chart__plot" style="height: {{ $height }}px">
            <div class="ez-chart__grid" aria-hidden="true">
                @foreach($ticks as $t)<i></i>@endforeach
            </div>

            <div class="ez-bars__track">
                @foreach($labels as $x => $label)
                    <div class="ez-bars__group" @mouseenter="i = {{ $x }}" @mouseleave="i = null" :class="{ 'is-dim': i !== null && i !== {{ $x }} }">
                        <div class="ez-bars__set">
                            @foreach($series as $s)
                                @php $v = $s['data'][$x] ?? 0; @endphp
                                <span class="ez-bars__bar"
                                      style="--h: {{ round(($v / $top) * 100, 3) }}%; --bar: {{ $s['color'] }}; --delay: {{ $x * 40 }}ms"
                                      title="{{ $s['name'] }}: {{ $v }}"></span>
                            @endforeach
                        </div>
                        <div class="ez-bars__tip" x-cloak x-show="i === {{ $x }}" x-transition.opacity.duration.120ms>
                            <p class="ez-chart__tip-label">{{ $label }}</p>
                            @foreach($series as $s)
                                <p class="ez-chart__tip-row"><i style="background: {{ $s['color'] }}"></i><span>{{ $s['name'] }}</span><b>{{ $s['data'][$x] ?? 0 }}</b></p>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="ez-chart__labels ez-chart__labels--bars">
        @foreach($labels as $l)<span>{{ $l }}</span>@endforeach
    </div>

    <div class="ez-chart__legend">
        @foreach($series as $s)<span><i style="background: {{ $s['color'] }}"></i>{{ $s['name'] }}</span>@endforeach
    </div>
</div>
