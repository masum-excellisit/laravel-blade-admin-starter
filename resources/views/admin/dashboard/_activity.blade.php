{{-- WIDGET: activity timeline. Needs $recentActivity. --}}
@php
    $tint = fn ($action) => match (true) {
        str_contains($action, 'create') => '#10b981',
        str_contains($action, 'delete') => '#ef4444',
        str_contains($action, 'update') => 'var(--c-primary)',
        str_contains($action, 'login') => '#0ea5e9',
        default => '#94a3b8',
    };
@endphp
<section class="ez-panel">
    <header class="ez-panel__head">
        <div>
            <h3 class="ez-panel__title">Activity</h3>
            <p class="ez-panel__sub">What changed recently</p>
        </div>
        @can('activity-logs.view')
            <a href="{{ route('admin.activity-logs.index') }}" class="ez-link">Full log <x-icon name="chevron-right" class="w-3.5 h-3.5" /></a>
        @endcan
    </header>
    <div class="ez-panel__body">
        @if($recentActivity->isEmpty())
            <x-empty-state icon="clock" message="No activity recorded yet." />
        @else
        <ol class="ez-feed">
            @foreach($recentActivity as $log)
                <li class="ez-feed__item" style="--tint: {{ $tint($log->action) }}">
                    <span class="ez-feed__dot" aria-hidden="true"></span>
                    <p class="ez-feed__text">
                        <b>{{ $log->user?->name ?? 'System' }}</b>
                        {{ $log->description ?: str_replace(['_', '.'], ' ', $log->action) }}
                    </p>
                    <p class="ez-feed__meta">
                        <x-icon name="clock" class="w-3 h-3" />{{ $log->created_at?->diffForHumans() }}
                        @if($log->subject_type)<span class="ez-feed__tag">{{ class_basename($log->subject_type) }}</span>@endif
                    </p>
                </li>
            @endforeach
        </ol>
        @endif
    </div>
</section>
