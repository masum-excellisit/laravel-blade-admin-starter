@extends('layouts.admin')
@section('title', 'Edit media')
@section('content')
<x-page-header title="Edit media" subtitle="Update metadata or replace this file while keeping the same media record.">
    <x-slot:actions>
        <x-btn :href="route('admin.media.index')" variant="outline">Back to media</x-btn>
    </x-slot:actions>
</x-page-header>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <x-card class="lg:col-span-1" title="Preview">
        @if(str_starts_with((string) $medium->mime, 'image/'))
            <img src="{{ $medium->url }}" alt="{{ $medium->alt_text }}" class="w-full rounded-xl object-cover border border-slate-200 dark:border-slate-700">
        @else
            <div class="h-48 rounded-xl bg-slate-100 dark:bg-slate-700 flex items-center justify-center text-slate-400 text-sm">File preview unavailable</div>
        @endif
        <dl class="mt-4 space-y-2 text-sm text-slate-500">
            <div>
                <dt class="font-semibold text-slate-700 dark:text-slate-200">Filename</dt>
                <dd>{{ $medium->name }}</dd>
            </div>
            <div>
                <dt class="font-semibold text-slate-700 dark:text-slate-200">Path</dt>
                <dd class="break-all">{{ $medium->path }}</dd>
            </div>
            <div>
                <dt class="font-semibold text-slate-700 dark:text-slate-200">Size</dt>
                <dd>{{ number_format($medium->size / 1024, 1) }} KB</dd>
            </div>
        </dl>
    </x-card>

    <x-card class="lg:col-span-2" title="Details">
        <form method="POST" action="{{ route('admin.media.update', $medium) }}" enctype="multipart/form-data" class="space-y-5">
            @csrf
            @method('PUT')
            <x-form.input name="folder" label="Folder" :value="$medium->folder" />
            <x-form.input name="alt_text" label="Alt text" :value="$medium->alt_text" />
            <x-form.input name="tags" label="Tags" :value="$medium->tags" hint="Use comma-separated tags." />
            <div>
                <x-form.label for="replacement">Replace file</x-form.label>
                <input type="file" name="replacement" id="replacement" class="w-full text-sm">
                <p class="mt-1 text-xs text-slate-400">Uploading a new file deletes the old file after this record is saved.</p>
                @error('replacement')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>
            <x-btn type="submit">Save changes</x-btn>
        </form>
    </x-card>
</div>
@endsection
