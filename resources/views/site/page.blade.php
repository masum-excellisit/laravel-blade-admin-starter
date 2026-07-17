@extends('layouts.app')
@section('title', $page->meta_title ?: $page->title)
@push('meta')
<x-seo-meta
    :title="$page->meta_title ?: $page->title"
    :description="$page->meta_description"
    :og-image="$page->og_image"
    :canonical="$page->canonical_url ?: url('/'.$page->slug)"
/>
@endpush
@section('content')
<div class="brand-gradient text-white">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 py-20 text-center">
        <h1 class="text-4xl sm:text-5xl font-bold">{{ $page->title }}</h1>
    </div>
</div>
<article class="max-w-3xl mx-auto px-4 sm:px-6 py-16 prose prose-slate max-w-none prose-headings:font-bold prose-a:text-primary">
    {!! $page->body !!}
</article>
@endsection
