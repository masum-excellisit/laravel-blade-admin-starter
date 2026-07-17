@extends('layouts.app')
@section('title', $job->title)
@section('content')
<section class="max-w-3xl mx-auto px-4 sm:px-6 py-16">
    <a href="{{ route('careers') }}" class="text-sm text-slate-500 hover:text-slate-800">← All roles</a>
    <h1 class="mt-4 text-4xl font-bold tracking-tight">{{ $job->title }}</h1>
    <p class="mt-2 text-slate-500">{{ $job->location }} · {{ str_replace('-', ' ', $job->employment_type) }}</p>

    <div class="prose prose-slate mt-10 max-w-none">
        <h2>About the role</h2>
        {!! $job->description !!}
        @if($job->requirements)
            <h2>Requirements</h2>
            {!! $job->requirements !!}
        @endif
    </div>

    <div class="mt-12 rounded-2xl border border-slate-200 p-6 bg-slate-50">
        <h2 class="text-xl font-semibold mb-4">Apply for this role</h2>
        @if(session('success'))
            <p class="mb-4 text-sm text-green-700 bg-green-50 rounded-xl px-4 py-3">{{ session('success') }}</p>
        @endif
        <form method="POST" action="{{ route('jobs.apply', $job->slug) }}" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <x-form.input name="name" label="Full name" :value="old('name')" required />
            <x-form.input name="email" type="email" label="Email" :value="old('email')" required />
            <x-form.input name="phone" label="Phone" :value="old('phone')" />
            <x-form.textarea name="cover_letter" label="Cover letter" :value="old('cover_letter')" rows="5" />
            <div>
                <x-form.label for="resume">Resume (PDF/DOC)</x-form.label>
                <input type="file" name="resume" id="resume" accept=".pdf,.doc,.docx"
                       class="block w-full text-sm text-slate-500 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-slate-100">
                @error('resume')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>
            <x-btn type="submit">Submit application</x-btn>
        </form>
    </div>
</section>
@endsection
