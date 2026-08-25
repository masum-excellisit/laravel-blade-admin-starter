{{--
    Dependency-free donut chart (SVG stroke-dasharray + Alpine hover).

    <x-chart.donut :data="[['label'=>'Posts','value'=>12,'color'=>'#6366f1']]" caption="Items" />

    Remove: delete this file and any <x-chart.donut> usage.
--}}
@props([
    'data' => [],
    'caption' => 'Total',
    'size' => 200,
    'thickness' => 16,
])
@php
    $total = array_sum(array_column($data, 'value'));
    $r = 50 - $thickness / 4;          // viewBox is 0 0 100 100
    $circ = 2 * M_PI * $r;
    $offset = 0;
    $segments = [];
    foreach ($data as $k => $d) {
        $share = $total > 0 ? $d['value'] / $total : 0;
        $len = $share * $circ;
        $segments[] = [
            'label' => $d['label'],
            'value' => $d['value'],
            'color' => $d['color'],
            'pct' => $total > 0 ? round($share * 100, 1) : 0,
            'dash' => round(max($len - 1.5, 0), 3).' '.round($circ - max($len - 1.5, 0), 3),
            'offset' => round(-$offset, 3),
            'delay' => $k * 70,
        ];
        $offset += $len;
    }
@endphp

<div class="ez-donut" x-data="{
        i: null,
        labels: @js(array_column($segments, 'label')),
        values: @js(array_map(fn ($s) => number_format($s['value']), $segments)),
    }">
    <div class="ez-donut__ring" style="width: {{ $size }}px; height: {{ $size }}px">
        <svg viewBox="0 0 100 100" role="img" aria-label="{{ $caption }} breakdown">
            <circle class="ez-donut__track" cx="50" cy="50" r="{{ $r }}" fill="none" stroke-width="{{ $thickness / 2 }}"/>
            @foreach($segments as $k => $s)
                <circle class="ez-donut__seg" cx="50" cy="50" r="{{ $r }}" fill="none"
                        stroke="{{ $s['color'] }}" stroke-width="{{ $thickness / 2 }}" stroke-linecap="round"
                        stroke-dasharray="{{ $s['dash'] }}" stroke-dashoffset="{{ $s['offset'] }}"
                        style="--circ: {{ round($circ, 3) }}; --delay: {{ $s['delay'] }}ms"
                        :class="{ 'is-dim': i !== null && i !== {{ $k }} }"
                        @mouseenter="i = {{ $k }}" @mouseleave="i = null"/>
            @endforeach
        </svg>
        <div class="ez-donut__center">
            <template x-if="i === null">
                <div>
                    <p class="ez-donut__value">{{ number_format($total) }}</p>
                    <p class="ez-donut__caption">{{ $caption }}</p>
                </div>
            </template>
            <template x-if="i !== null">
                <div>
                    <p class="ez-donut__value" x-text="values[i]"></p>
                    <p class="ez-donut__caption" x-text="labels[i]"></p>
                </div>
            </template>
        </div>
    </div>

    <ul class="ez-donut__legend">
        @foreach($segments as $k => $s)
            <li @mouseenter="i = {{ $k }}" @mouseleave="i = null" :class="{ 'is-dim': i !== null && i !== {{ $k }} }">
                <i style="background: {{ $s['color'] }}"></i>
                <span>{{ $s['label'] }}</span>
                <b>{{ number_format($s['value']) }}</b>
                <em>{{ $s['pct'] }}%</em>
            </li>
        @endforeach
    </ul>
</div>
