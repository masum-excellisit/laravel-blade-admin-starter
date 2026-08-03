{{-- WIDGET: create shortcuts. Self-contained — edit the $actions array to change the list. --}}
@php
    $actions = [
        ['label' => 'New post', 'route' => 'admin.posts.create', 'can' => 'posts.view', 'icon' => 'pencil', 'tint' => 'var(--c-primary)'],
        ['label' => 'New page', 'route' => 'admin.pages.create', 'can' => 'pages.view', 'icon' => 'document', 'tint' => 'var(--c-secondary)'],
        ['label' => 'Upload media', 'route' => 'admin.media.index', 'can' => 'media.view', 'icon' => 'upload', 'tint' => '#0ea5e9'],
        ['label' => 'Add customer', 'route' => 'admin.customers.create', 'can' => 'customers.view', 'icon' => 'users', 'tint' => '#10b981'],
        ['label' => 'New service', 'route' => 'admin.services.create', 'can' => 'services.view', 'icon' => 'bolt', 'tint' => '#f59e0b'],
        ['label' => 'Settings', 'route' => 'admin.settings.edit', 'can' => 'settings.view', 'icon' => 'server', 'tint' => 'var(--c-accent)'],
    ];
    $actions = array_values(array_filter($actions, fn ($a) => auth()->user()->can($a['can']) && \Illuminate\Support\Facades\Route::has($a['route'])));
@endphp
@if($actions)
<section class="ez-panel">
    <header class="ez-panel__head">
        <div>
            <h3 class="ez-panel__title">Quick actions</h3>
            <p class="ez-panel__sub">Jump straight into the work</p>
        </div>
    </header>
    <div class="ez-panel__body">
        <div class="ez-actions">
            @foreach($actions as $a)
                <a href="{{ route($a['route']) }}" class="ez-action" style="--tint: {{ $a['tint'] }}">
                    <span class="ez-action__icon"><x-icon :name="$a['icon']" class="w-5 h-5" /></span>
                    <span class="ez-action__label">{{ $a['label'] }}</span>
                    <x-icon name="chevron-right" class="w-4 h-4 ez-action__go" />
                </a>
            @endforeach
        </div>
    </div>
</section>
@endif
