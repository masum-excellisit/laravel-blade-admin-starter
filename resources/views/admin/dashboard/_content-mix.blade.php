{{-- WIDGET: content breakdown donut. Needs $contentMix (DashboardAnalytics::contentMix). --}}
<section class="ez-panel">
    <header class="ez-panel__head">
        <div>
            <h3 class="ez-panel__title">Content mix</h3>
            <p class="ez-panel__sub">Everything published across the site</p>
        </div>
    </header>
    <div class="ez-panel__body">
        @if(count($contentMix))
            <x-chart.donut :data="$contentMix" caption="Items" :size="188" />
        @else
            <x-empty-state message="No content created yet." />
        @endif
    </div>
</section>
