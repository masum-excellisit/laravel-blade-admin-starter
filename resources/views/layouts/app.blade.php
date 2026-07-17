<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', $settings['site_name'] ?? config('app.name'))</title>
    <meta name="description" content="@yield('meta_description', $settings['site_tagline'] ?? '')">
    @include('partials.theme')
    @include('partials.assets')
</head>
<body class="antialiased bg-white text-slate-800">
<header x-data="{ open:false }" class="sticky top-0 z-40 bg-white/80 backdrop-blur border-b border-slate-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 h-16 flex items-center justify-between">
        <x-app-logo />
        <nav class="hidden md:flex items-center gap-7 text-sm font-medium text-slate-600">
            <x-site.menu location="header" class="hover:text-primary transition" />
        </nav>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.dashboard') }}" class="hidden sm:inline"><x-btn size="sm" variant="outline">Admin</x-btn></a>
            <button x-on:click="open=!open" class="md:hidden text-slate-600"><svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M4 6h16M4 12h16M4 18h16"/></svg></button>
        </div>
    </div>
    <div x-show="open" x-cloak class="md:hidden border-t border-slate-100 px-4 py-3 flex flex-col gap-2 text-sm font-medium text-slate-600">
        <x-site.menu location="header" class="py-1.5 hover:text-primary" />
    </div>
</header>

<main>@yield('content')</main>

<footer class="bg-slate-900 text-slate-300 mt-24">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-14 grid grid-cols-1 md:grid-cols-3 gap-10">
        <div>
            <x-app-logo :dark="true" />
            <p class="mt-4 text-sm text-slate-400 max-w-xs">{{ $settings['site_tagline'] ?? '' }}</p>
        </div>
        <div>
            <h4 class="font-semibold text-white mb-3">Links</h4>
            <div class="flex flex-col gap-2 text-sm"><x-site.menu location="footer" class="text-slate-400 hover:text-white" /></div>
        </div>
        <div>
            <h4 class="font-semibold text-white mb-3">Contact</h4>
            <p class="text-sm text-slate-400">{{ $settings['contact_email'] ?? '' }}</p>
            <p class="text-sm text-slate-400">{{ $settings['contact_phone'] ?? '' }}</p>
            <p class="text-sm text-slate-400">{{ $settings['contact_address'] ?? '' }}</p>
        </div>
    </div>
    <div class="border-t border-white/10 py-5 text-center text-xs text-slate-500">&copy; {{ date('Y') }} {{ $settings['site_name'] ?? config('app.name') }}. All rights reserved.</div>
</footer>
<x-flash />
@include('partials.assets-scripts')
</body>
</html>
