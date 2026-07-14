<!DOCTYPE html>
<html lang="en"
      x-data="{
          dark: (localStorage.getItem('theme') || '{{ $settings['theme_mode'] ?? 'light' }}') === 'dark',
          sidebar: false,
          collapsed: localStorage.getItem('sidebar_collapsed') === '1'
      }"
      x-init="$watch('dark', v => localStorage.setItem('theme', v ? 'dark' : 'light'));
              $watch('collapsed', v => localStorage.setItem('sidebar_collapsed', v ? '1' : '0'))"
      :class="{ 'dark': dark }">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin') · {{ $settings['site_name'] ?? config('app.name') }}</title>
    @include('partials.theme')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="admin-bg text-slate-800 dark:text-slate-200 antialiased">
<div class="min-h-screen">
    <!-- Mobile overlay -->
    <div x-show="sidebar" x-cloak x-on:click="sidebar=false" x-transition.opacity class="fixed inset-0 z-30 bg-slate-900/60 backdrop-blur-sm lg:hidden"></div>

    <!-- Sidebar -->
    <aside class="admin-sidebar fixed inset-y-0 left-0 z-40 flex flex-col transform transition-all duration-300 lg:translate-x-0"
           :class="[sidebar ? 'translate-x-0' : '-translate-x-full lg:translate-x-0', collapsed ? 'w-20' : 'w-64']">
        <div class="h-16 flex items-center px-5 shrink-0 border-b border-white/10 overflow-hidden">
            <div x-show="!collapsed" x-cloak><x-app-logo :dark="true" /></div>
            <div x-show="collapsed" x-cloak class="mx-auto">
                <span class="h-9 w-9 rounded-xl brand-gradient flex items-center justify-center text-white text-lg shadow-lg">◆</span>
            </div>
        </div>
        <div class="flex-1 overflow-y-auto py-2" :class="collapsed ? 'px-2' : ''">
            @include('partials.admin-sidebar')
        </div>
        <div class="shrink-0 p-3 border-t border-white/10">
            <button x-on:click="collapsed=!collapsed" class="hidden lg:flex items-center gap-2 w-full px-3 py-2 rounded-xl text-slate-400 hover:bg-white/5 hover:text-white text-sm transition">
                <svg class="w-5 h-5 shrink-0 transition-transform" :class="collapsed && 'rotate-180'" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"/></svg>
                <span x-show="!collapsed">Collapse</span>
            </button>
        </div>
    </aside>

    <!-- Main -->
    <div class="transition-all duration-300" :class="collapsed ? 'lg:pl-20' : 'lg:pl-64'">
        <header class="sticky top-0 z-20 h-16 bg-white/70 dark:bg-slate-900/70 backdrop-blur-xl border-b border-slate-200/60 dark:border-white/5 flex items-center gap-3 px-4 sm:px-6">
            <!-- Mobile: open drawer -->
            <button x-on:click="sidebar=true" class="lg:hidden p-2 -ml-2 rounded-lg text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
            <!-- Desktop: collapse toggle -->
            <button x-on:click="collapsed=!collapsed" class="hidden lg:flex p-2 -ml-2 rounded-lg text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800" title="Toggle sidebar">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>

            <div class="ml-auto flex items-center gap-1.5">
                <button x-on:click="dark=!dark" class="p-2 rounded-lg text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800 transition" title="Toggle theme">
                    <svg x-show="!dark" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path stroke-linecap="round" d="M21 12.8A9 9 0 1111.2 3a7 7 0 009.8 9.8z"/></svg>
                    <svg x-show="dark" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path stroke-linecap="round" d="M12 3v2m0 14v2m9-9h-2M5 12H3m15.4 6.4l-1.4-1.4M7 7L5.6 5.6m12.8 0L17 7M7 17l-1.4 1.4M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                </button>
                <a href="{{ url('/') }}" target="_blank" class="p-2 rounded-lg text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800 transition" title="View site">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path stroke-linecap="round" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0zM3.6 9h16.8M3.6 15h16.8M12 3a15 15 0 010 18M12 3a15 15 0 000 18"/></svg>
                </a>
                <div class="w-px h-6 bg-slate-200 dark:bg-white/10 mx-1"></div>
                <!-- Profile -->
                <div x-data="{ o: false }" class="relative">
                    <button x-on:click="o=!o" class="flex items-center gap-2.5 pl-2 pr-1 py-1 rounded-full hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                        <span class="hidden sm:block text-sm font-medium">{{ auth()->user()->name }}</span>
                        <span class="h-8 w-8 rounded-full brand-gradient text-white text-xs font-semibold flex items-center justify-center shadow">{{ auth()->user()->initials() }}</span>
                    </button>
                    <div x-show="o" x-cloak x-on:click.outside="o=false" x-transition class="absolute right-0 mt-2 w-52 bg-white dark:bg-slate-800 rounded-2xl shadow-xl border border-slate-200 dark:border-slate-700 py-2">
                        <div class="px-4 py-2 border-b border-slate-100 dark:border-slate-700">
                            <p class="text-sm font-semibold truncate">{{ auth()->user()->name }}</p>
                            <p class="text-xs text-slate-400 truncate">{{ auth()->user()->email }}</p>
                        </div>
                        <a href="{{ route('admin.profile.edit') }}" class="flex items-center gap-2.5 px-4 py-2 text-sm hover:bg-slate-50 dark:hover:bg-slate-700 mt-1">
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path stroke-linecap="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>Profile
                        </a>
                        @can('settings.view')<a href="{{ route('admin.settings.edit') }}" class="flex items-center gap-2.5 px-4 py-2 text-sm hover:bg-slate-50 dark:hover:bg-slate-700">
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path stroke-linecap="round" d="M10.3 4.3a2 2 0 013.4 0M12 15a3 3 0 100-6 3 3 0 000 6z"/></svg>Settings
                        </a>@endcan
                        <form method="POST" action="{{ route('admin.logout') }}" class="border-t border-slate-100 dark:border-slate-700 mt-1 pt-1">@csrf
                            <button class="flex items-center gap-2.5 w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 dark:hover:bg-slate-700">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path stroke-linecap="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>Log out
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        <main class="p-4 sm:p-6 lg:p-8 max-w-[1600px] mx-auto">
            @yield('content')
        </main>
    </div>
</div>
<x-flash />
@stack('scripts')
</body>
</html>
