{{-- WIDGET: inbound volume bars, last 8 weeks. Needs $engagement (DashboardAnalytics::engagement). --}}
<section class="ez-panel">
    <header class="ez-panel__head">
        <div>
            <h3 class="ez-panel__title">Inbound</h3>
            <p class="ez-panel__sub"><b>{{ number_format($engagement['total']) }}</b> submissions over 8 weeks</p>
        </div>
        @can('messages.view')
            <a href="{{ route('admin.messages.index') }}" class="ez-link">Inbox <x-icon name="chevron-right" class="w-3.5 h-3.5" /></a>
        @endcan
    </header>
    <div class="ez-panel__body">
        <x-chart.bar :labels="$engagement['labels']" :series="$engagement['series']" :height="200" />
    </div>
</section>
