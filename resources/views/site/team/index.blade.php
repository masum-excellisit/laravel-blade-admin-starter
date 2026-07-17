@extends('layouts.app')
@section('title', 'Team')
@section('content')
<section class="max-w-7xl mx-auto px-4 sm:px-6 py-16">
    <div class="max-w-2xl mb-12">
        <p class="text-sm font-semibold uppercase tracking-wide brand-gradient-text">Team</p>
        <h1 class="mt-2 text-4xl font-bold tracking-tight">Meet the team</h1>
        <p class="mt-3 text-lg text-slate-500">The people behind our work.</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        @forelse($teamMembers as $teamMember)
        <article class="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm">
            @if($teamMember->photo)
                <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($teamMember->photo) }}" alt="{{ $teamMember->name }}" class="h-28 w-28 rounded-full object-cover mb-5">
            @endif
            <h2 class="font-semibold text-xl">{{ $teamMember->name }}</h2>
            @if($teamMember->role_title)<p class="mt-1 text-sm brand-gradient-text font-medium">{{ $teamMember->role_title }}</p>@endif
            @if($teamMember->bio)<p class="mt-4 text-sm text-slate-500">{{ $teamMember->bio }}</p>@endif
            <div class="mt-5 flex flex-wrap gap-3 text-sm">
                @if($teamMember->email)<a class="text-slate-500 hover:text-slate-800" href="mailto:{{ $teamMember->email }}">Email</a>@endif
                @if($teamMember->linkedin)<a class="text-slate-500 hover:text-slate-800" href="{{ $teamMember->linkedin }}">LinkedIn</a>@endif
            </div>
        </article>
        @empty
        <p class="text-slate-400 col-span-3">No team members published yet.</p>
        @endforelse
    </div>
</section>
@endsection
