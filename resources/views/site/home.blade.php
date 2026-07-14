@extends('layouts.app')
@section('title', ($settings['site_name'] ?? config('app.name')).' — '.($settings['site_tagline'] ?? ''))
@section('content')
<!-- Hero -->
<section class="relative overflow-hidden brand-gradient text-white">
    <div class="absolute -top-32 -right-32 w-[30rem] h-[30rem] rounded-full bg-white/10 blur-3xl"></div>
    <div class="absolute -bottom-40 -left-20 w-96 h-96 rounded-full bg-black/10 blur-3xl"></div>
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 py-28 text-center">
        <span class="inline-block px-4 py-1.5 rounded-full bg-white/15 text-sm font-medium mb-6">✨ Built with Laravel + Tailwind</span>
        <h1 class="text-4xl sm:text-6xl font-bold tracking-tight max-w-3xl mx-auto leading-[1.1]">{{ $settings['site_name'] ?? config('app.name') }}</h1>
        <p class="mt-6 text-lg text-white/80 max-w-2xl mx-auto">{{ $settings['site_tagline'] ?? 'A premium starter kit.' }}</p>
        <div class="mt-9 flex items-center justify-center gap-3">
            <a href="{{ route('blog.index') }}"><x-btn variant="secondary" size="lg" class="!bg-white !text-slate-900">Read the blog</x-btn></a>
            <a href="{{ route('contact') }}"><x-btn size="lg" class="!bg-white/15 backdrop-blur border border-white/30">Get in touch</x-btn></a>
        </div>
    </div>
</section>

<!-- Features -->
<section class="max-w-7xl mx-auto px-4 sm:px-6 py-24">
    <div class="text-center max-w-2xl mx-auto mb-14">
        <h2 class="text-3xl font-bold">Everything you need to launch</h2>
        <p class="mt-3 text-slate-500">A custom admin panel, dynamic content, roles &amp; permissions, and a themeable public site.</p>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        @foreach([['Custom admin', 'A premium, gradient, mobile-first Blade admin panel with dashboard, users, and settings.'],['Dynamic content', 'Pages, a blog, categories and drag-friendly menus — all editable, no code required.'],['Fully themeable', 'Change the gradient in Settings and both the admin and public site restyle instantly.']] as $f)
        <div class="rounded-2xl p-7 border border-slate-100 shadow-sm hover:shadow-lg transition">
            <div class="h-11 w-11 rounded-xl brand-gradient flex items-center justify-center text-white mb-4">◆</div>
            <h3 class="font-semibold text-lg">{{ $f[0] }}</h3>
            <p class="mt-2 text-sm text-slate-500">{{ $f[1] }}</p>
        </div>
        @endforeach
    </div>
</section>

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
