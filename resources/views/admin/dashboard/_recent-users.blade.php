{{-- WIDGET: newest customers. Needs $recentUsers. --}}
<section class="ez-panel">
    <header class="ez-panel__head">
        <div>
            <h3 class="ez-panel__title">New customers</h3>
            <p class="ez-panel__sub">Most recent sign-ups</p>
        </div>
        @can('customers.view')
            <a href="{{ route('admin.customers.index') }}" class="ez-link">All customers <x-icon name="chevron-right" class="w-3.5 h-3.5" /></a>
        @endcan
    </header>
    <div class="ez-panel__body">
        @if($recentUsers->isEmpty())
            <x-empty-state icon="users" message="No customers yet." />
        @else
        <ul class="ez-list">
            @foreach($recentUsers as $user)
                <li class="ez-list__row">
                    <span class="ez-avatar">{{ $user->initials() }}</span>
                    <div class="ez-list__main">
                        <p class="ez-list__title">{{ $user->name }}</p>
                        <p class="ez-list__meta">{{ $user->email }}</p>
                    </div>
                    <span class="ez-list__time">{{ $user->created_at?->diffForHumans(null, true) }}</span>
                </li>
            @endforeach
        </ul>
        @endif
    </div>
</section>
