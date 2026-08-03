{{-- WIDGET: publishing ratio + environment. Needs $system (DashboardAnalytics::system). --}}
@php
    $ratio = $system['posts'] > 0 ? $system['published'] / $system['posts'] : 0;
    $circ = 2 * M_PI * 42;
    $bytes = $system['mediaBytes'];
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $u = 0;
    while ($bytes >= 1024 && $u < count($units) - 1) { $bytes /= 1024; $u++; }
    $mediaSize = round($bytes, $u > 1 ? 1 : 0).' '.$units[$u];
@endphp
<section class="ez-panel">
    <header class="ez-panel__head">
        <div>
            <h3 class="ez-panel__title">Health</h3>
            <p class="ez-panel__sub">Publishing &amp; environment</p>
        </div>
    </header>
    <div class="ez-panel__body">
        <div class="ez-health">
            <div class="ez-gauge">
                <svg viewBox="0 0 100 100" role="img" aria-label="{{ round($ratio * 100) }}% of posts published">
                    <circle class="ez-gauge__track" cx="50" cy="50" r="42" fill="none" stroke-width="9"/>
                    <circle class="ez-gauge__value" cx="50" cy="50" r="42" fill="none" stroke-width="9" stroke-linecap="round"
                            style="--circ: {{ round($circ, 2) }}"
                            stroke-dasharray="{{ round($circ * $ratio, 2) }} {{ round($circ, 2) }}"/>
                </svg>
                <div class="ez-gauge__center">
                    <p class="ez-gauge__pct">{{ round($ratio * 100) }}%</p>
                    <p class="ez-gauge__cap">published</p>
                </div>
            </div>

            <dl class="ez-facts">
                <div><dt>Published</dt><dd>{{ number_format($system['published']) }}</dd></div>
                <div><dt>Drafts</dt><dd>{{ number_format($system['drafts']) }}</dd></div>
                <div><dt>Media files</dt><dd>{{ number_format($system['mediaCount']) }}</dd></div>
                <div><dt>Library size</dt><dd>{{ $mediaSize }}</dd></div>
            </dl>
        </div>

        <div class="ez-chips">
            <span class="ez-chip">Laravel {{ $system['laravel'] }}</span>
            <span class="ez-chip">PHP {{ $system['php'] }}</span>
            <span class="ez-chip">{{ $system['cache'] }} cache</span>
            <span class="ez-chip">{{ $system['queue'] }} queue</span>
            <span @class(['ez-chip', 'is-warn' => $system['debug'] || $system['env'] !== 'production'])>{{ $system['env'] }}{{ $system['debug'] ? ' · debug' : '' }}</span>
        </div>
    </div>
</section>
