@extends('layouts.app')
@section('title', cms('how-it-works', 'intro', 'title', 'How it works'))
@section('content')
<section class="max-w-7xl mx-auto px-4 sm:px-6 py-16">
    <div class="max-w-2xl mb-14">
        <h1 class="text-4xl font-bold tracking-tight">{{ cms('how-it-works', 'intro', 'title', 'How it works') }}</h1>
        <p class="mt-3 text-lg text-slate-500">{{ cms('how-it-works', 'intro', 'subtitle') }}</p>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        @foreach([1,2,3] as $i)
            @if($t = cms('how-it-works', 'steps', "step_{$i}_title"))
            <div class="rounded-2xl p-7 border border-slate-100 shadow-sm">
                <div class="h-10 w-10 rounded-full brand-gradient text-white flex items-center justify-center font-bold mb-4">{{ $i }}</div>
                <h2 class="font-semibold text-lg">{{ $t }}</h2>
                <p class="mt-2 text-sm text-slate-500">{{ cms('how-it-works', 'steps', "step_{$i}_body") }}</p>
            </div>
            @endif
        @endforeach
    </div>
</section>
@endsection
