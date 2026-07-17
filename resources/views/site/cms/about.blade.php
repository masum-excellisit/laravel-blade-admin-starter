@extends('layouts.app')
@section('title', cms('about', 'intro', 'title', 'About'))
@section('content')
<section class="max-w-7xl mx-auto px-4 sm:px-6 py-16">
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-start">
        <div>
            <h1 class="text-4xl font-bold tracking-tight">{{ cms('about', 'intro', 'title', 'About') }}</h1>
            <div class="prose prose-slate mt-6 max-w-none">{!! cms('about', 'intro', 'body') !!}</div>
            @if(cms('about', 'mission', 'title'))
                <div class="mt-10 rounded-2xl border border-slate-100 p-6 bg-slate-50">
                    <h2 class="text-xl font-semibold">{{ cms('about', 'mission', 'title') }}</h2>
                    <p class="mt-2 text-slate-600">{{ cms('about', 'mission', 'body') }}</p>
                </div>
            @endif
        </div>
        @if($img = cms_image('about', 'intro', 'image'))
            <img src="{{ $img }}" alt="" class="rounded-2xl w-full object-cover shadow-lg">
        @endif
    </div>
</section>
@endsection
