@extends('layouts.admin')
@section('title', 'Media')
@section('content')
<x-page-header title="Media library" subtitle="Uploaded images and files." />
@can('media.create')
<x-card class="mb-6">
    <form method="POST" action="{{ route('admin.media.store') }}" enctype="multipart/form-data" class="flex flex-col sm:flex-row gap-3 items-start sm:items-center">@csrf
        <input type="file" name="files[]" multiple class="text-sm flex-1">
        <x-btn type="submit">Upload</x-btn>
    </form>
</x-card>
@endcan
<div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
    @forelse($media as $item)
    <div class="group relative rounded-xl overflow-hidden border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800">
        @if(str_starts_with($item->mime, 'image/'))
        <img src="{{ $item->url }}" class="aspect-square w-full object-cover">
        @else
        <div class="aspect-square flex items-center justify-center text-slate-400 text-xs p-2 text-center">{{ $item->name }}</div>
        @endif
        <div class="p-2">
            <p class="text-xs truncate text-slate-500" title="{{ $item->name }}">{{ $item->name }}</p>
            <div class="flex items-center justify-between mt-1">
                <button onclick="navigator.clipboard.writeText('{{ $item->url }}')" class="text-[10px] brand-gradient-text">Copy URL</button>
                @can('media.delete')<form method="POST" action="{{ route('admin.media.destroy', $item) }}" onsubmit="return confirm('Delete?')">@csrf @method('DELETE')<button class="text-[10px] text-red-500">Delete</button></form>@endcan
            </div>
        </div>
    </div>
    @empty<p class="col-span-full text-center text-slate-400 py-10">No media uploaded.</p>@endforelse
</div>
<div class="mt-4">{{ $media->links() }}</div>
@endsection
