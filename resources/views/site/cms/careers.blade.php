@extends('layouts.app')
@section('title', cms('careers', 'intro', 'title', 'Careers'))
@section('content')
<section class="max-w-7xl mx-auto px-4 sm:px-6 py-16">
    <div class="max-w-2xl mb-10">
        <h1 class="text-4xl font-bold tracking-tight">{{ cms('careers', 'intro', 'title', 'Careers') }}</h1>
        <p class="mt-3 text-lg text-slate-500">{{ cms('careers', 'intro', 'subtitle') }}</p>
        <div class="prose prose-slate mt-6">{!! cms('careers', 'intro', 'body') !!}</div>
    </div>
    <div class="space-y-4 mb-16">
        @forelse($jobs as $job)
        <a href="{{ route('jobs.show', $job->slug) }}" class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 rounded-2xl border border-slate-100 p-6 hover:shadow-md transition bg-white">
            <div>
                <h2 class="font-semibold text-lg">{{ $job->title }}</h2>
                <p class="text-sm text-slate-500 mt-1">{{ $job->location }} · {{ str_replace('-', ' ', $job->employment_type) }}</p>
            </div>
            <span class="text-sm font-medium brand-gradient-text">View role →</span>
        </a>
        @empty
        <p class="text-slate-400">No open roles right now.</p>
        @endforelse
    </div>

    @if($testimonials->count())
    <h2 class="text-2xl font-bold mb-6">Team voices</h2>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        @foreach($testimonials as $t)
        <blockquote class="rounded-2xl p-6 border border-slate-100">
            <p class="text-slate-600">“{{ $t->quote }}”</p>
            <footer class="mt-4 text-sm font-semibold">{{ $t->author_name }}</footer>
        </blockquote>
        @endforeach
    </div>
    @endif
</section>
@endsection
