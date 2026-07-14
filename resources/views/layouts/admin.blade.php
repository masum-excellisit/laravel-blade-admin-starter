<!DOCTYPE html>
<html lang="en" x-data="{ dark: (localStorage.theme ?? '{{ $settings['theme_mode'] ?? 'light' }}') === 'dark', sidebar: false }"
      x-init="$watch('dark', v => localStorage.theme = v ? 'dark' : 'light')"
      :class="{ 'dark': dark }">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin') · {{ $settings['site_name'] ?? config('app.name') }}</title>
    @include('partials.theme')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-100 dark:bg-slate-900 text-slate-800 dark:text-slate-200 antialiased">
<div class="min-h-screen">
    <!-- Mobile overlay -->
    <div x-show="sidebar" x-cloak x-on:click="sidebar=false" class="fixed inset-0 z-30 bg-slate-900/50 lg:hidden"></div>

    <!-- Sidebar -->
    <aside class="fixed inset-y-0 left-0 z-40 w-64 transform transition-transform lg:translate-x-0"
           :class="sidebar ? 'translate-x-0' : '-translate-x-full'"
           style="background: var(--sidebar-bg);">
        <div class="h-16 flex items-center px-5 border-b border-white/10">
            <x-app-logo :dark="true" />
        </div>
        <div class="overflow-y-auto h-[calc(100vh-4rem)]">
            @include('partials.admin-sidebar')
        </div>
    </aside>

    <!-- Main -->
    <div class="lg:pl-64">
        <header class="sticky top-0 z-20 h-16 bg-white/80 dark:bg-slate-800/80 backdrop-blur border-b border-slate-200/70 dark:border-slate-700/60 flex items-center gap-4 px-4 sm:px-6">
            <button x-on:click="sidebar=true" class="lg:hidden text-slate-500">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
            <div class="ml-auto flex items-center gap-2">
                <button x-on:click="dark=!dark" class="p-2 rounded-lg text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-700">
                    <svg x-show="!dark" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path stroke-linecap="round" d="M21 12.8A9 9 0 1111.2 3a7 7 0 009.8 9.8z"/></svg>
                    <svg x-show="dark" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path stroke-linecap="round" d="M12 3v2m0 14v2m9-9h-2M5 12H3m15.4 6.4l-1.4-1.4M7 7L5.6 5.6m12.8 0L17 7M7 17l-1.4 1.4M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                </button>
                <a href="{{ url('/') }}" target="_blank" class="p-2 rounded-lg text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-700" title="View site">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path stroke-linecap="round" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0zM3.6 9h16.8M3.6 15h16.8M12 3a15 15 0 010 18M12 3a15 15 0 000 18"/></svg>
                </a>
                <!-- Profile -->
                <div x-data="{ o: false }" class="relative">
                    <button x-on:click="o=!o" class="flex items-center gap-2 pl-2 pr-1 py-1 rounded-full hover:bg-slate-100 dark:hover:bg-slate-700">
                        <span class="hidden sm:block text-sm font-medium">{{ auth()->user()->name }}</span>
                        <span class="h-8 w-8 rounded-full brand-gradient text-white text-xs font-semibold flex items-center justify-center">{{ auth()->user()->initials() }}</span>
                    </button>
                    <div x-show="o" x-cloak x-on:click.outside="o=false" x-transition class="absolute right-0 mt-2 w-48 bg-white dark:bg-slate-800 rounded-xl shadow-xl border border-slate-200 dark:border-slate-700 py-1.5">
                        <a href="{{ route('admin.profile.edit') }}" class="block px-4 py-2 text-sm hover:bg-slate-50 dark:hover:bg-slate-700">Profile</a>
                        @can('settings.view')<a href="{{ route('admin.settings.edit') }}" class="block px-4 py-2 text-sm hover:bg-slate-50 dark:hover:bg-slate-700">Settings</a>@endcan
                        <form method="POST" action="{{ route('admin.logout') }}" class="border-t border-slate-100 dark:border-slate-700 mt-1 pt-1">@csrf
                            <button class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 dark:hover:bg-slate-700">Log out</button>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        <main class="p-4 sm:p-6 lg:p-8">
            @yield('content')
        </main>
    </div>
</div>
<x-flash />
@stack('scripts')
</body>
</html>
