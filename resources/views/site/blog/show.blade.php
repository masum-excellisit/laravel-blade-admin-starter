@extends('layouts.app')
@section('title', $post->meta_title ?: $post->title)
@php
    $postOgImage = $post->og_image ?: ($post->featured_image ? \Illuminate\Support\Facades\Storage::disk('public')->url($post->featured_image) : null);
@endphp
@push('meta')
<x-seo-meta
    :title="$post->meta_title ?: $post->title"
    :description="$post->meta_description ?: $post->excerpt"
    :og-image="$postOgImage"
    :canonical="$post->canonical_url ?: route('blog.show', $post->slug)"
/>
@endpush
@section('content')
<div class="brand-gradient text-white">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 py-20 text-center">
        @if($post->category)<a href="{{ route('blog.category', $post->category->slug) }}" class="text-sm font-semibold text-white/80 uppercase tracking-wide">{{ $post->category->name }}</a>@endif
        <h1 class="mt-3 text-4xl sm:text-5xl font-bold leading-tight">{{ $post->title }}</h1>
        <p class="mt-4 text-white/70 text-sm">{{ $post->published_at?->format('F j, Y') }} · {{ $post->author?->name }}</p>
    </div>
</div>
@if($post->featured_image)
<div class="max-w-4xl mx-auto px-4 sm:px-6 -mt-10"><img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($post->featured_image) }}" class="rounded-2xl shadow-xl w-full"></div>
@endif
<article class="max-w-3xl mx-auto px-4 sm:px-6 py-16 prose prose-slate max-w-none prose-a:text-primary">{!! $post->body !!}</article>

@if($related->count())
<section class="max-w-7xl mx-auto px-4 sm:px-6 pb-24">
    <h2 class="text-2xl font-bold mb-8">Related posts</h2>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">@foreach($related as $post)@include('site.blog._card', ['post' => $post])@endforeach</div>
</section>
@endif
@endsection
