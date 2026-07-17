@extends('layouts.app')
@section('title', $service->title)
@section('content')
<article class="max-w-3xl mx-auto px-4 sm:px-6 py-16">
    <a href="{{ route('services.index') }}" class="text-sm text-slate-500 hover:text-slate-800">← All services</a>
    <h1 class="mt-4 text-4xl font-bold tracking-tight">{{ $service->title }}</h1>
    @if($service->excerpt)<p class="mt-3 text-lg text-slate-500">{{ $service->excerpt }}</p>@endif
    @if($service->imageUrl())
        <img src="{{ $service->imageUrl() }}" alt="" class="mt-8 w-full rounded-2xl object-cover max-h-96">
    @endif
    <div class="prose prose-slate mt-10 max-w-none">{!! $service->body !!}</div>
</article>
@endsection
