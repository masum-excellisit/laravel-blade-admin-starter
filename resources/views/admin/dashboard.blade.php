@extends('layouts.admin')
@section('title', 'Dashboard')
@section('content')
<x-page-header title="Dashboard" subtitle="Welcome back, {{ auth()->user()->name }}." />

<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5 mb-8">
    @foreach($stats as $stat)
        @if($stat['can'] === null || auth()->user()->can($stat['can']))
        <a href="{{ route($stat['route']) }}" class="group relative overflow-hidden rounded-2xl p-6 brand-gradient text-white shadow-lg shadow-primary/20">
            <div class="absolute -right-6 -top-6 w-24 h-24 rounded-full bg-white/10"></div>
            <p class="text-sm font-medium text-white/80">{{ $stat['label'] }}</p>
            <p class="text-4xl font-bold mt-2">{{ $stat['value'] }}</p>
        </a>
        @endif
    @endforeach
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <x-card title="Recent posts">
        <div class="space-y-3">
            @forelse($recentPosts as $post)
            <div class="flex items-center justify-between">
                <div>
                    <p class="font-medium text-slate-800 dark:text-slate-100">{{ $post->title }}</p>
                    <p class="text-xs text-slate-400">{{ $post->created_at->diffForHumans() }}</p>
                </div>
                <x-badge :color="$post->status === 'published' ? 'green' : 'slate'">{{ $post->status }}</x-badge>
            </div>
            @empty<p class="text-sm text-slate-400">No posts yet.</p>@endforelse
        </div>
    </x-card>
    <x-card title="Recent users">
        <div class="space-y-3">
            @forelse($recentUsers as $user)
            <div class="flex items-center gap-3">
                <span class="h-9 w-9 rounded-full brand-gradient text-white text-xs font-semibold flex items-center justify-center">{{ $user->initials() }}</span>
                <div>
                    <p class="font-medium text-slate-800 dark:text-slate-100">{{ $user->name }}</p>
                    <p class="text-xs text-slate-400">{{ $user->email }}</p>
                </div>
            </div>
            @empty<p class="text-sm text-slate-400">No users yet.</p>@endforelse
        </div>
    </x-card>
</div>
@endsection
