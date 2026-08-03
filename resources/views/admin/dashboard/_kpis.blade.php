{{-- WIDGET: KPI cards. Needs $kpis (DashboardAnalytics::kpis). --}}
<div class="ez-kpis">
    @foreach($kpis as $kpi)
        @continue($kpi['can'] !== null && ! auth()->user()->can($kpi['can']))
        <a href="{{ route($kpi['route']) }}" class="ez-kpi" style="--tint: {{ $kpi['color'] }}">
            <span class="ez-kpi__glow" aria-hidden="true"></span>

            <div class="ez-kpi__top">
                <span class="ez-kpi__icon"><x-icon :name="$kpi['icon']" class="w-5 h-5" /></span>
                @if($kpi['delta'] === null)
                    <span class="ez-pill is-new"><x-icon name="sparkles" class="w-3 h-3" />new</span>
                @elseif($kpi['delta'] > 0)
                    <span class="ez-pill is-up"><x-icon name="trend-up" class="w-3 h-3" />{{ $kpi['delta'] }}%</span>
                @elseif($kpi['delta'] < 0)
                    <span class="ez-pill is-down"><x-icon name="trend-down" class="w-3 h-3" />{{ abs($kpi['delta']) }}%</span>
                @else
                    <span class="ez-pill">—</span>
                @endif
            </div>

            <p class="ez-kpi__label">{{ $kpi['label'] }}</p>
            <p class="ez-kpi__value"
               x-data="{ n: 0 }"
               x-init="(() => {
                   const t = {{ (int) $kpi['value'] }};
                   if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) { n = t; return; }
                   const start = performance.now();
                   const tick = now => {
                       const p = Math.min((now - start) / 900, 1);
                       n = Math.round(t * (1 - Math.pow(1 - p, 3)));
                       if (p < 1) requestAnimationFrame(tick);
                   };
                   requestAnimationFrame(tick);
               })()"
               x-text="n.toLocaleString()">{{ number_format($kpi['value']) }}</p>
            <p class="ez-kpi__meta">{{ $kpi['new'] }} in the last 30 days</p>

            <x-chart.sparkline :data="$kpi['spark']" :color="$kpi['color']" class="ez-kpi__spark" :height="46" />
        </a>
    @endforeach
</div>
