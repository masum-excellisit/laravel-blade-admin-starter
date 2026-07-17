<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $headline }} - {{ $settings['site_name'] ?? config('app.name') }}</title>
    @include('partials.theme')
    @include('partials.assets')
</head>
<body class="min-h-screen antialiased bg-slate-950 text-white flex items-center justify-center px-4">
    <main class="max-w-xl text-center">
        <div class="mx-auto mb-8 h-16 w-16 rounded-2xl brand-gradient flex items-center justify-center shadow-lg">
            <span class="text-2xl font-bold">!</span>
        </div>
        <p class="text-sm font-semibold uppercase tracking-[0.25em] text-white/50">Maintenance</p>
        <h1 class="mt-4 text-4xl sm:text-5xl font-bold tracking-tight">{{ $headline }}</h1>
        <p class="mt-5 text-lg text-white/70">{{ $message }}</p>
    </main>
</body>
</html>
