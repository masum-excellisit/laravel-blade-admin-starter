<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Error' }} · {{ $settings['site_name'] ?? config('app.name') }}</title>
    @include('partials.favicon')
    @include('partials.theme')
    @include('partials.assets')
</head>
<body class="antialiased bg-slate-50 text-slate-800 min-h-screen flex flex-col">
<header class="border-b border-slate-200 bg-white">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 h-14 flex items-center">
        <x-app-logo />
    </div>
</header>
<main class="flex-1 flex items-center justify-center px-4 py-16">
    <div class="w-full max-w-md text-center">
        <p class="text-sm font-semibold uppercase tracking-wider text-primary">{{ $code ?? '' }}</p>
        <h1 class="mt-2 text-3xl font-bold tracking-tight text-slate-900">{{ $heading ?? 'Something went wrong' }}</h1>
        <p class="mt-3 text-sm text-slate-500">{{ $message ?? '' }}</p>
        <div class="mt-8 flex flex-wrap items-center justify-center gap-3">
            <x-btn :href="$backUrl ?? url('/')">{{ $backLabel ?? 'Back to home' }}</x-btn>
            @isset($secondaryUrl)
                <x-btn variant="outline" :href="$secondaryUrl">{{ $secondaryLabel ?? 'Dashboard' }}</x-btn>
            @endisset
        </div>
    </div>
</main>
<footer class="py-6 text-center text-xs text-slate-400">
    &copy; {{ date('Y') }} {{ $settings['site_name'] ?? config('app.name') }}
</footer>
@include('partials.assets-scripts')
</body>
</html>
