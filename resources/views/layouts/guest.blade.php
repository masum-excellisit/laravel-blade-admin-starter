<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Sign in') · {{ $settings['site_name'] ?? config('app.name') }}</title>
    @include('partials.theme')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased bg-slate-50 text-slate-800">
<div class="min-h-screen grid lg:grid-cols-2">
    <!-- Brand side — project name only -->
    <div class="relative hidden lg:flex items-center justify-center p-12 brand-gradient text-white overflow-hidden">
        <div class="absolute -top-24 -right-24 w-96 h-96 rounded-full bg-white/10 blur-3xl"></div>
        <div class="absolute bottom-0 -left-20 w-80 h-80 rounded-full bg-black/10 blur-3xl"></div>
        <div class="relative flex flex-col items-center gap-5">
            @if($settings['site_logo'] ?? false)
                <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($settings['site_logo']) }}" alt="logo" class="h-14 w-auto">
            @else
                <span class="h-16 w-16 rounded-2xl bg-white/15 backdrop-blur flex items-center justify-center text-3xl shadow-lg">◆</span>
            @endif
            <h1 class="text-3xl font-bold tracking-tight text-center">{{ $settings['site_name'] ?? config('app.name') }}</h1>
        </div>
    </div>
    <!-- Form side -->
    <div class="flex items-center justify-center p-6 sm:p-12">
        <div class="w-full max-w-sm">
            @yield('content')
        </div>
    </div>
</div>
<x-flash />
</body>
</html>
