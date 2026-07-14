<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Sign in') · {{ $settings['site_name'] ?? config('app.name') }}</title>
    @include('partials.theme')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased">
<div class="min-h-screen grid lg:grid-cols-2">
    <!-- Brand side -->
    <div class="relative hidden lg:flex flex-col justify-between p-12 brand-gradient text-white overflow-hidden">
        <div class="absolute -top-24 -right-24 w-96 h-96 rounded-full bg-white/10 blur-3xl"></div>
        <div class="absolute bottom-0 -left-20 w-80 h-80 rounded-full bg-black/10 blur-3xl"></div>
        <x-app-logo :dark="true" class="relative text-2xl" />
        <div class="relative">
            <h1 class="text-4xl font-bold leading-tight">{{ $settings['site_name'] ?? config('app.name') }}</h1>
            <p class="mt-4 text-white/80 max-w-md">{{ $settings['site_tagline'] ?? 'A premium admin starter kit.' }}</p>
        </div>
        <p class="relative text-sm text-white/60">&copy; {{ date('Y') }} {{ $settings['site_name'] ?? config('app.name') }}</p>
    </div>
    <!-- Form side -->
    <div class="flex items-center justify-center p-6 sm:p-12 bg-slate-50">
        <div class="w-full max-w-md">
            @yield('content')
        </div>
    </div>
</div>
<x-flash />
</body>
</html>
