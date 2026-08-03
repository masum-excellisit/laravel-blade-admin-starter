{{-- WIDGET: growth area chart with 30d / 12m toggle. Needs $trend (DashboardAnalytics::trend). --}}
@php
    $totals = [
        'days' => array_sum(array_merge(...array_column($trend['days']['series'], 'data'))),
        'months' => array_sum(array_merge(...array_column($trend['months']['series'], 'data'))),
    ];
@endphp
<section class="ez-panel" x-data="{ range: 'days' }">
    <header class="ez-panel__head">
        <div>
            <h3 class="ez-panel__title">Growth</h3>
            <p class="ez-panel__sub">
                <b x-text="range === 'days' ? '{{ number_format($totals['days']) }}' : '{{ number_format($totals['months']) }}'"></b>
                new records <span x-text="range === 'days' ? 'in the last 30 days' : 'in the last 12 months'"></span>
            </p>
        </div>
        <div class="ez-seg" role="tablist" aria-label="Chart range">
            <button type="button" role="tab" :aria-selected="range === 'days'" :class="{ 'is-on': range === 'days' }" @click="range = 'days'">30 days</button>
            <button type="button" role="tab" :aria-selected="range === 'months'" :class="{ 'is-on': range === 'months' }" @click="range = 'months'">12 months</button>
        </div>
    </header>

    <div class="ez-panel__body">
        <div x-show="range === 'days'">
            <x-chart.area :labels="$trend['days']['labels']" :series="$trend['days']['series']" :height="280" />
        </div>
        <div x-show="range === 'months'" x-cloak>
            <x-chart.area :labels="$trend['months']['labels']" :series="$trend['months']['series']" :height="280" />
        </div>
    </div>
</section>
