@php
$cmsPages = collect(config('cms.pages', []))->map(fn ($cfg, $key) => [
    'label' => $cfg['title'],
    'route' => 'admin.cms.edit',
    'params' => ['page' => $key],
    'can' => 'cms.view',
])->values()->all();

$nav = [
    ['label' => 'Dashboard', 'route' => 'admin.dashboard', 'icon' => 'M3 12l9-9 9 9M4.5 10.5V21h5v-6h5v6h5V10.5', 'can' => null],
    [
        'label' => 'CMS Management',
        'icon' => 'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10',
        'can' => 'cms.view',
        'children' => array_merge(
            [['label' => 'All pages', 'route' => 'admin.cms.index', 'params' => [], 'can' => 'cms.view']],
            $cmsPages
        ),
    ],
    ['label' => 'Users', 'route' => 'admin.customers.index', 'icon' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z', 'can' => 'customers.view'],
    ['label' => 'Services', 'route' => 'admin.services.index', 'icon' => 'M4 6h16M4 10h16M4 14h10M4 18h10', 'can' => 'services.view'],
    ['label' => 'Testimonials', 'route' => 'admin.testimonials.index', 'icon' => 'M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z', 'can' => 'testimonials.view'],
    ['label' => 'Jobs', 'route' => 'admin.jobs.index', 'icon' => 'M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z', 'can' => 'jobs.view'],
    ['label' => 'Job Applications', 'route' => 'admin.job-applications.index', 'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.6L19 9.4V19a2 2 0 01-2 2z', 'can' => 'job-applications.view'],
    ['label' => 'Pages', 'route' => 'admin.pages.index', 'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.6L19 9.4V19a2 2 0 01-2 2z', 'can' => 'pages.view'],
    ['label' => 'Posts', 'route' => 'admin.posts.index', 'icon' => 'M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10l6 6v8a2 2 0 01-2 2zM9 8h6M9 12h6M9 16h4', 'can' => 'posts.view'],
    ['label' => 'Categories', 'route' => 'admin.categories.index', 'icon' => 'M7 7h.01M7 3h5a2 2 0 011.4.6l7 7a2 2 0 010 2.8l-5 5a2 2 0 01-2.8 0l-7-7A2 2 0 013 12V7a4 4 0 014-4z', 'can' => 'categories.view'],
    ['label' => 'Menus', 'route' => 'admin.menus.index', 'icon' => 'M4 6h16M4 12h16M4 18h16', 'can' => 'menus.view'],
    ['label' => 'Media', 'route' => 'admin.media.index', 'icon' => 'M4 5a2 2 0 012-2h12a2 2 0 012 2v14a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm4 8l2.5 3 3.5-4.5L19 19', 'can' => 'media.view'],
    ['label' => 'Messages', 'route' => 'admin.messages.index', 'icon' => 'M3 8l9 6 9-6M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z', 'can' => 'messages.view'],
    ['label' => 'Blocks', 'route' => 'admin.blocks.index', 'icon' => 'M4 5h16M4 12h16M4 19h16', 'can' => 'blocks.view'],
    ['label' => 'Forms', 'route' => 'admin.forms.index', 'icon' => 'M9 12h6M9 16h6M8 4h8a2 2 0 012 2v12a2 2 0 01-2 2H8a2 2 0 01-2-2V6a2 2 0 012-2z', 'can' => 'forms.view'],
    // [admin-module nav] — do not remove; make:admin-module injects here (before Settings).
    ['label' => 'Settings', 'route' => 'admin.settings.edit', 'icon' => 'M10.3 4.3a2 2 0 013.4 0l.5 1a2 2 0 002.3 1l1-.3a2 2 0 012.4 2.4l-.3 1a2 2 0 001 2.3l1 .5a2 2 0 010 3.4l-1 .5a2 2 0 00-1 2.3l.3 1a2 2 0 01-2.4 2.4l-1-.3a2 2 0 00-2.3 1l-.5 1a2 2 0 01-3.4 0l-.5-1a2 2 0 00-2.3-1l-1 .3a2 2 0 01-2.4-2.4l.3-1a2 2 0 00-1-2.3l-1-.5a2 2 0 010-3.4l1-.5a2 2 0 001-2.3l-.3-1A2 2 0 016.5 6l1 .3a2 2 0 002.3-1zM12 15a3 3 0 100-6 3 3 0 000 6z', 'can' => 'settings.view'],
    ['label' => 'Admin Users', 'route' => 'admin.users.index', 'icon' => 'M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-1.13a4 4 0 10-4-4 4 4 0 004 4z', 'can' => 'users.view'],
    ['label' => 'Roles', 'route' => 'admin.roles.index', 'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z', 'can' => 'roles.view'],
    ['label' => 'Permissions', 'route' => 'admin.permissions.index', 'icon' => 'M15 7a2 2 0 012 2m4 0a6 6 0 01-7.7 5.7L10 18H8v2H6v2H2v-4l6.3-6.3A6 6 0 1121 9z', 'can' => 'permissions.view'],
];
@endphp
<nav class="px-3 py-3 space-y-1" x-data="{ open: {{ request()->routeIs('admin.cms*') ? 'true' : 'false' }} }">
    <p x-show="!collapsed" x-cloak class="px-3 pt-2 pb-1 text-[11px] font-semibold uppercase tracking-wider text-white/30">Menu</p>
    @foreach($nav as $item)
        @if(isset($item['children']))
            @if($item['can'] === null || auth()->user()->can($item['can']))
                @php
                    $groupActive = collect($item['children'])->contains(function ($child) {
                        return request()->routeIs($child['route']) && (
                            empty($child['params']) || collect($child['params'])->every(fn ($v, $k) => request()->route($k) == $v || request()->segment(3) == $v)
                        );
                    }) || request()->routeIs('admin.cms*');
                @endphp
                <div>
                    <button type="button" x-on:click="open = !open" title="{{ $item['label'] }}"
                            class="group flex items-center gap-3 w-full px-3 py-3 rounded-xl text-sm font-medium transition-all {{ $groupActive ? 'bg-white/10 text-white' : 'text-slate-300/90 hover:bg-white/10 hover:text-white' }}"
                            :class="collapsed && 'justify-center'">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $item['icon'] }}"/></svg>
                        <span x-show="!collapsed" x-cloak class="flex-1 text-left">{{ $item['label'] }}</span>
                        <svg x-show="!collapsed" x-cloak class="w-4 h-4 transition-transform" :class="open && 'rotate-180'" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div x-show="open && !collapsed" x-cloak class="mt-1 ml-3 pl-3 border-l border-white/10 space-y-0.5">
                        @foreach($item['children'] as $child)
                            @if($child['can'] === null || auth()->user()->can($child['can']))
                                @php
                                    $childActive = request()->routeIs($child['route']) && (
                                        empty($child['params']['page']) || request()->route('page') === ($child['params']['page'] ?? null)
                                    );
                                @endphp
                                <a href="{{ route($child['route'], $child['params'] ?? []) }}"
                                   class="block px-3 py-2 rounded-lg text-sm transition {{ $childActive ? 'brand-gradient text-white shadow' : 'text-slate-400 hover:text-white hover:bg-white/5' }}">
                                    {{ $child['label'] }}
                                </a>
                            @endif
                        @endforeach
                    </div>
                </div>
            @endif
        @elseif($item['can'] === null || auth()->user()->can($item['can']))
            @php $active = request()->routeIs(str_replace('.index', '', $item['route']).'*') || request()->routeIs($item['route']); @endphp
            <a href="{{ route($item['route']) }}" title="{{ $item['label'] }}"
               class="group flex items-center gap-3 px-3 py-3 rounded-xl text-sm font-medium transition-all {{ $active ? 'brand-gradient text-white shadow-lg shadow-black/20' : 'text-slate-300/90 hover:bg-white/10 hover:text-white' }}"
               :class="collapsed && 'justify-center'">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $item['icon'] }}"/></svg>
                <span x-show="!collapsed" x-cloak>{{ $item['label'] }}</span>
            </a>
        @endif
    @endforeach
</nav>
