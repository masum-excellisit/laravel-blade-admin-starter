@extends('layouts.app')
@section('title', 'FAQs')
@section('content')
<section class="max-w-4xl mx-auto px-4 sm:px-6 py-16">
    <div class="mb-10">
        <p class="text-sm font-semibold uppercase tracking-wide brand-gradient-text">FAQs</p>
        <h1 class="mt-2 text-4xl font-bold tracking-tight">Frequently asked questions</h1>
        <p class="mt-3 text-lg text-slate-500">Answers to common questions about working with us.</p>
    </div>

    <div class="space-y-4">
        @forelse($faqs as $faq)
        <article class="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm">
            @if($faq->category)
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">{{ $faq->category }}</p>
            @endif
            <h2 class="mt-1 text-lg font-semibold">{{ $faq->question }}</h2>
            <div class="prose prose-slate mt-3 max-w-none text-slate-600">{!! nl2br(e($faq->answer)) !!}</div>
        </article>
        @empty
        <p class="text-slate-400">No FAQs published yet.</p>
        @endforelse
    </div>
</section>
@endsection
