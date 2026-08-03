@extends('layouts.admin')
@section('title', 'Dashboard')

{{--
    ADMIN DASHBOARD
    ---------------
    Every block below is an independent widget. To remove one, delete (or comment
    out) its @include line — nothing else breaks. To remove the premium styling
    entirely, drop the @push('styles') block and public/css/admin-dashboard.css.
    To remove the analytics layer, delete app/Services/DashboardAnalytics.php,
    resources/views/components/chart/ and the widgets that use them.

        _kpis           KPI cards + sparklines      needs $kpis
        _quick-actions  create shortcuts            self-contained
        _trend          growth area chart           needs $trend
        _content-mix    content donut               needs $contentMix
        _engagement     inbound bar chart           needs $engagement
        _system         publishing gauge + env      needs $system
        _recent-posts   latest posts                needs $recentPosts
        _recent-users   newest customers            needs $recentUsers
        _activity       activity timeline           needs $recentActivity
--}}

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin-dashboard.css') }}">
@endpush

@section('content')
@php
    $hour = now()->hour;
    $greeting = $hour < 12 ? 'Good morning' : ($hour < 18 ? 'Good afternoon' : 'Good evening');
@endphp

{{-- Hero --}}
<section class="ez-hero">
    <div class="ez-hero__mesh" aria-hidden="true"></div>
    <div class="ez-hero__inner">
        <div>
            <p class="ez-hero__eyebrow">{{ now()->format('l, j F Y') }}</p>
            <h1 class="ez-hero__title">{{ $greeting }}, {{ auth()->user()->name }}.</h1>
            <p class="ez-hero__sub">Here is how {{ $settings['site_name'] ?? config('app.name') }} is doing today.</p>
        </div>
        <div class="ez-hero__actions">
            <a href="{{ url('/') }}" target="_blank" rel="noopener" class="ez-hero__btn is-ghost">
                <x-icon name="external" class="w-4 h-4" /> View site
            </a>
            @can('posts.view')
                <a href="{{ route('admin.posts.create') }}" class="ez-hero__btn">
                    <x-icon name="plus" class="w-4 h-4" /> New post
                </a>
            @endcan
        </div>
    </div>
</section>

@include('admin.dashboard._kpis')

{{-- Spans come from admin-dashboard.css (.ez-md-* / .ez-xl-*); default is full width. --}}
<div class="ez-grid">
    <div>@include('admin.dashboard._quick-actions')</div>

    <div class="ez-xl-8">@include('admin.dashboard._trend')</div>
    <div class="ez-md-6 ez-xl-4">@include('admin.dashboard._content-mix')</div>

    <div class="ez-xl-7">@include('admin.dashboard._engagement')</div>
    <div class="ez-md-6 ez-xl-5">@include('admin.dashboard._system')</div>

    <div class="ez-md-6 ez-xl-4">@include('admin.dashboard._recent-posts')</div>
    <div class="ez-md-6 ez-xl-4">@include('admin.dashboard._recent-users')</div>
    <div class="ez-xl-4">@include('admin.dashboard._activity')</div>
</div>
@endsection
