@props(['dark' => false])
@php $logo = $settings['site_logo'] ?? null; $name = $settings['site_name'] ?? config('app.name'); @endphp
<a href="{{ url('/') }}" {{ $attributes->merge(['class' => 'flex items-center gap-2.5 font-bold']) }}>
    @if($logo)
        <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($logo) }}" alt="{{ $name }}" class="h-8 w-auto">
    @else
        <span class="h-9 w-9 rounded-xl brand-gradient flex items-center justify-center text-white text-lg shadow-lg shadow-primary/30">◆</span>
        <span class="{{ $dark ? 'text-white' : 'text-slate-800 dark:text-white' }} text-lg tracking-tight">{{ $name }}</span>
    @endif
</a>
