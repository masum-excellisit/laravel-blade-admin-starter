@extends('layouts.app')
@section('title', 'Portfolio')
@section('content')
<section class="max-w-7xl mx-auto px-4 sm:px-6 py-16">
    <div class="max-w-2xl mb-12">
        <p class="text-sm font-semibold uppercase tracking-wide brand-gradient-text">Portfolio</p>
        <h1 class="mt-2 text-4xl font-bold tracking-tight">Selected work</h1>
        <p class="mt-3 text-lg text-slate-500">A few projects and case studies from our team.</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        @forelse($portfolioItems as $portfolioItem)
        <a href="{{ route('portfolio.show', $portfolioItem->slug) }}" class="block rounded-2xl border border-slate-100 bg-white p-6 shadow-sm hover:shadow-lg transition">
            @if($portfolioItem->image)
                <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($portfolioItem->image) }}" alt="{{ $portfolioItem->title }}" class="h-44 w-full object-cover rounded-xl mb-5">
            @endif
            @if($portfolioItem->client)<p class="text-xs font-semibold uppercase tracking-wide text-slate-400">{{ $portfolioItem->client }}</p>@endif
            <h2 class="mt-1 font-semibold text-xl">{{ $portfolioItem->title }}</h2>
            @if($portfolioItem->excerpt)<p class="mt-2 text-sm text-slate-500">{{ $portfolioItem->excerpt }}</p>@endif
            <span class="mt-5 inline-block text-sm font-medium brand-gradient-text">View project -&gt;</span>
        </a>
        @empty
        <p class="text-slate-400 col-span-3">No portfolio items published yet.</p>
        @endforelse
    </div>
</section>
@endsection
