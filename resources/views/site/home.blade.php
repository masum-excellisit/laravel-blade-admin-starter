@extends('layouts.app')
@section('title', cms('home', 'hero', 'headline', $settings['site_name'] ?? config('app.name')))
@section('content')
@php
    $heroImage = cms_image('home', 'hero', 'image');
@endphp
<!-- Hero -->
<section class="relative overflow-hidden brand-gradient text-white {{ $heroImage ? '' : '' }}">
    @if($heroImage)
        <div class="absolute inset-0">
            <img src="{{ $heroImage }}" alt="" class="h-full w-full object-cover opacity-35">
            <div class="absolute inset-0 bg-slate-900/40"></div>
        </div>
    @else
        <div class="absolute -top-32 -right-32 w-[30rem] h-[30rem] rounded-full bg-white/10 blur-3xl"></div>
        <div class="absolute -bottom-40 -left-20 w-96 h-96 rounded-full bg-black/10 blur-3xl"></div>
    @endif
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 py-28 text-center">
        @if($eyebrow = cms('home', 'hero', 'eyebrow'))
            <span class="inline-block px-4 py-1.5 rounded-full bg-white/15 text-sm font-medium mb-6">{{ $eyebrow }}</span>
        @endif
        <h1 class="text-4xl sm:text-6xl font-bold tracking-tight max-w-3xl mx-auto leading-[1.1]">
            {{ cms('home', 'hero', 'headline', $settings['site_name'] ?? config('app.name')) }}
        </h1>
        <p class="mt-6 text-lg text-white/80 max-w-2xl mx-auto">
            {{ cms('home', 'hero', 'subheadline', $settings['site_tagline'] ?? 'A premium starter kit.') }}
        </p>
        <div class="mt-9 flex items-center justify-center gap-3">
            @if($pText = cms('home', 'hero', 'cta_primary_text'))
                <a href="{{ cms('home', 'hero', 'cta_primary_url', route('blog.index')) }}"><x-btn variant="secondary" size="lg" class="!bg-white !text-slate-900">{{ $pText }}</x-btn></a>
            @endif
            @if($sText = cms('home', 'hero', 'cta_secondary_text'))
                <a href="{{ cms('home', 'hero', 'cta_secondary_url', route('contact')) }}"><x-btn size="lg" class="!bg-white/15 backdrop-blur border border-white/30">{{ $sText }}</x-btn></a>
            @endif
        </div>
    </div>
</section>

<!-- Features -->
<section class="max-w-7xl mx-auto px-4 sm:px-6 py-24">
    <div class="text-center max-w-2xl mx-auto mb-14">
        <h2 class="text-3xl font-bold">{{ cms('home', 'features', 'title', 'Everything you need to launch') }}</h2>
        <p class="mt-3 text-slate-500">{{ cms('home', 'features', 'subtitle') }}</p>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        @foreach([1,2,3] as $i)
            @if($t = cms('home', 'features', "item_{$i}_title"))
            <div class="rounded-2xl p-7 border border-slate-100 shadow-sm hover:shadow-lg transition">
                <div class="h-11 w-11 rounded-xl brand-gradient flex items-center justify-center text-white mb-4">◆</div>
                <h3 class="font-semibold text-lg">{{ $t }}</h3>
                <p class="mt-2 text-sm text-slate-500">{{ cms('home', 'features', "item_{$i}_body") }}</p>
            </div>
            @endif
        @endforeach
    </div>
</section>

@if(($services ?? collect())->count())
<section class="bg-slate-50 dark:bg-slate-900/40 py-24">
    <div class="max-w-7xl mx-auto px-4 sm:px-6">
        <div class="flex items-end justify-between mb-10">
            <h2 class="text-3xl font-bold">Services</h2>
            <a href="{{ route('services.index') }}" class="text-sm font-medium brand-gradient-text">View all →</a>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @foreach($services as $service)
            <a href="{{ route('services.show', $service->slug) }}" class="block rounded-2xl p-7 bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700 hover:shadow-lg transition">
                <h3 class="font-semibold text-lg">{{ $service->title }}</h3>
                <p class="mt-2 text-sm text-slate-500">{{ $service->excerpt }}</p>
            </a>
            @endforeach
        </div>
    </div>
</section>
@endif

@if(($testimonials ?? collect())->count())
<section class="max-w-7xl mx-auto px-4 sm:px-6 py-24">
    <h2 class="text-3xl font-bold text-center mb-12">What people say</h2>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        @foreach($testimonials as $t)
        <blockquote class="rounded-2xl p-7 border border-slate-100 shadow-sm">
            <p class="text-slate-600">“{{ $t->quote }}”</p>
            <footer class="mt-5">
                <p class="font-semibold">{{ $t->author_name }}</p>
                <p class="text-sm text-slate-400">{{ $t->author_title }}</p>
            </footer>
        </blockquote>
        @endforeach
    </div>
</section>
@endif

<!-- Latest posts -->
@if($posts->count())
<section class="max-w-7xl mx-auto px-4 sm:px-6 pb-24">
    <div class="flex items-end justify-between mb-10">
        <h2 class="text-3xl font-bold">Latest from the blog</h2>
        <a href="{{ route('blog.index') }}" class="text-sm font-medium brand-gradient-text">View all →</a>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        @foreach($posts as $post)
        @include('site.blog._card', ['post' => $post])
        @endforeach
    </div>
</section>
@endif
@endsection
