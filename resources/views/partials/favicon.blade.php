@if(filled($settings['site_favicon'] ?? null))
    <link rel="icon" href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($settings['site_favicon']) }}">
    <link rel="apple-touch-icon" href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($settings['site_favicon']) }}">
@endif
