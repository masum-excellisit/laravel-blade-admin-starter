@extends('layouts.app')
@section('title', $portfolioItem->title)
@section('content')
<article class="max-w-3xl mx-auto px-4 sm:px-6 py-16">
    <a href="{{ route('portfolio.index') }}" class="text-sm text-slate-500 hover:text-slate-800">&lt;- All portfolio</a>
    @if($portfolioItem->client)<p class="mt-6 text-sm font-semibold uppercase tracking-wide brand-gradient-text">{{ $portfolioItem->client }}</p>@endif
    <h1 class="mt-2 text-4xl font-bold tracking-tight">{{ $portfolioItem->title }}</h1>
    @if($portfolioItem->excerpt)<p class="mt-3 text-lg text-slate-500">{{ $portfolioItem->excerpt }}</p>@endif
    @if($portfolioItem->image)
        <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($portfolioItem->image) }}" alt="{{ $portfolioItem->title }}" class="mt-8 w-full rounded-2xl object-cover max-h-96">
    @endif
    @if($portfolioItem->body)<div class="prose prose-slate mt-10 max-w-none">{!! nl2br(e($portfolioItem->body)) !!}</div>@endif
    @if($portfolioItem->project_url)
        <p class="mt-8"><a href="{{ $portfolioItem->project_url }}" class="font-medium brand-gradient-text">Visit project</a></p>
    @endif
</article>
@endsection
