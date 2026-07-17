@extends('layouts.app')
@section('title', cms('services', 'intro', 'title', 'Services'))
@section('content')
<section class="max-w-7xl mx-auto px-4 sm:px-6 py-16">
    <div class="max-w-2xl mb-12">
        <h1 class="text-4xl font-bold tracking-tight">{{ cms('services', 'intro', 'title', 'Services') }}</h1>
        <p class="mt-3 text-lg text-slate-500">{{ cms('services', 'intro', 'subtitle') }}</p>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        @forelse($services as $service)
        <a href="{{ route('services.show', $service->slug) }}" class="block rounded-2xl p-7 border border-slate-100 shadow-sm hover:shadow-lg transition">
            @if($service->imageUrl())
                <img src="{{ $service->imageUrl() }}" alt="" class="h-40 w-full object-cover rounded-xl mb-4">
            @endif
            <h2 class="font-semibold text-xl">{{ $service->title }}</h2>
            <p class="mt-2 text-sm text-slate-500">{{ $service->excerpt }}</p>
        </a>
        @empty
        <p class="text-slate-400 col-span-3">No services published yet.</p>
        @endforelse
    </div>
</section>
@endsection
