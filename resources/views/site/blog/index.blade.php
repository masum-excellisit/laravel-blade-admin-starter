@extends('layouts.app')
@section('title', 'Blog — '.($settings['site_name'] ?? config('app.name')))
@section('content')
<div class="brand-gradient text-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-20 text-center">
        <h1 class="text-4xl sm:text-5xl font-bold">{{ isset($category) ? $category->name : 'The Blog' }}</h1>
        <p class="mt-3 text-white/80">Insights, updates and stories.</p>
    </div>
</div>
<div class="max-w-7xl mx-auto px-4 sm:px-6 py-16">
    @if($categories->count())
    <div class="flex flex-wrap gap-2 mb-10 justify-center">
        <a href="{{ route('blog.index') }}" class="px-4 py-2 rounded-full text-sm font-medium {{ !isset($category) ? 'brand-gradient text-white' : 'bg-slate-100 text-slate-600' }}">All</a>
        @foreach($categories as $cat)
        <a href="{{ route('blog.category', $cat->slug) }}" class="px-4 py-2 rounded-full text-sm font-medium {{ (isset($category) && $category->id===$cat->id) ? 'brand-gradient text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">{{ $cat->name }} ({{ $cat->posts_count }})</a>
        @endforeach
    </div>
    @endif
    @if($posts->count())
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        @foreach($posts as $post)@include('site.blog._card', ['post' => $post])@endforeach
    </div>
    <div class="mt-10">{{ $posts->links() }}</div>
    @else
    <p class="text-center text-slate-400 py-16">No posts here yet.</p>
    @endif
</div>
@endsection
