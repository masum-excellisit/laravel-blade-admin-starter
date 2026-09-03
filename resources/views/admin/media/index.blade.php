@extends('layouts.admin')
@section('title', 'Media')
@section('content')
<x-page-header title="Media library" subtitle="Uploaded images and files." />

@can('media.create')
<x-card class="mb-6" title="Upload">
    <form method="POST" action="{{ route('admin.media.store') }}" enctype="multipart/form-data" class="space-y-4"
          x-data="{
              dragging: false,
              count: 0,
              pick(list) {
                  this.count = list ? list.length : 0;
              }
          }">@csrf
        <label
            for="files"
            class="block rounded-2xl border-2 border-dashed px-6 py-10 text-center transition cursor-pointer"
            :class="dragging ? 'border-primary bg-indigo-50/60 dark:bg-indigo-950/30' : 'border-slate-300 dark:border-slate-600 bg-slate-50/80 dark:bg-slate-900/40'"
            x-on:dragover.prevent="dragging = true"
            x-on:dragleave.prevent="dragging = false"
            x-on:drop.prevent="
                dragging = false;
                const input = document.getElementById('files');
                if (input && $event.dataTransfer.files.length) {
                    input.files = $event.dataTransfer.files;
                    pick(input.files);
                }
            "
        >
            <p class="text-sm font-medium text-slate-700 dark:text-slate-200">Drag and drop files here</p>
            <p class="mt-1 text-xs text-slate-400">or click to browse</p>
            <input type="file" name="files[]" id="files" multiple class="sr-only"
                   x-on:change="pick($event.target.files)">
            <p class="mt-3 text-xs text-slate-400" x-text="count ? (count + ' file(s) selected') : 'Images and documents welcome'"></p>
            @error('files')<p class="mt-2 text-xs text-red-500">{{ $message }}</p>@enderror
            @error('files.*')<p class="mt-2 text-xs text-red-500">{{ $message }}</p>@enderror
        </label>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
            <x-form.input name="folder" label="Folder" placeholder="Optional folder" />
            <x-form.input name="alt_text" label="Alt text" placeholder="Optional alt text" />
            <x-form.input name="tags" label="Tags" placeholder="Comma-separated tags" />
        </div>
        <x-btn type="submit">Upload</x-btn>
    </form>
</x-card>
@endcan

<div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4 mb-4">
    <x-search placeholder="Search files…">
        <x-slot:filters>
            <select name="folder" onchange="this.form.submit()" class="rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm brand-ring shadow-sm px-3.5 py-2.5">
                <option value="">All folders</option>
                @foreach($folders as $folder)
                    <option value="{{ $folder }}" @selected(request('folder') === $folder)>{{ $folder }}</option>
                @endforeach
            </select>
        </x-slot:filters>
    </x-search>
    @can('media.delete')
        <form method="POST" action="{{ route('admin.media.cleanup') }}" onsubmit="return confirm('Delete all unreferenced media records and files?')">
            @csrf
            <x-btn type="submit" variant="ghost" size="sm" class="!text-slate-500">Cleanup unused</x-btn>
        </form>
    @endcan
</div>

<x-table bulk :columns="[
    ['key' => null, 'label' => 'Preview', 'sortable' => false],
    ['key' => 'name', 'label' => 'Filename', 'sortable' => true],
    ['key' => 'folder', 'label' => 'Folder', 'sortable' => true],
    ['key' => null, 'label' => 'Metadata', 'sortable' => false],
    ['key' => null, 'label' => 'Type', 'sortable' => false],
    ['key' => 'created_at', 'label' => 'Uploaded', 'sortable' => true],
    ['key' => null, 'label' => '', 'sortable' => false],
]">
    <x-slot:toolbar>
        <x-bulk-actions :action="route('admin.media.bulk')" :options="['delete' => 'Delete selected']" />
    </x-slot:toolbar>
    @forelse($media as $item)
    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/60">
        <x-table-checkbox :id="$item->id" />
        <td class="px-4 py-3 w-16">
            @if(str_starts_with($item->mime, 'image/'))
            <img src="{{ $item->url }}" alt="" class="h-12 w-12 rounded-lg object-cover border border-slate-200 dark:border-slate-700">
            @else
            <span class="h-12 w-12 rounded-lg bg-slate-100 dark:bg-slate-700 flex items-center justify-center text-slate-400 text-xs">file</span>
            @endif
        </td>
        <td class="px-4 py-3">
            <p class="font-medium truncate max-w-xs" title="{{ $item->name }}">{{ $item->name }}</p>
            <p class="text-xs text-slate-400">{{ number_format($item->size / 1024, 1) }} KB</p>
            <p class="text-xs text-slate-400 truncate max-w-xs" title="{{ $item->path }}">{{ $item->path }}</p>
        </td>
        <td class="px-4 py-3 text-slate-500 text-xs">{{ $item->folder ?: '—' }}</td>
        <td class="px-4 py-3 text-slate-500 text-xs">
            @if($item->alt_text)<p><span class="font-semibold">Alt:</span> {{ $item->alt_text }}</p>@endif
            @if($item->tags)<p><span class="font-semibold">Tags:</span> {{ $item->tags }}</p>@endif
            @if(! $item->alt_text && ! $item->tags)<span>—</span>@endif
        </td>
        <td class="px-4 py-3 text-slate-500 text-xs">{{ $item->mime ?? '—' }}</td>
        <td class="px-4 py-3 text-slate-500">{{ $item->created_at->format('M j, Y') }}</td>
        <td class="px-4 py-3">
            <div class="flex items-center justify-end gap-1">
                <x-icon-btn icon="copy" type="button" label="Copy URL" onclick="navigator.clipboard.writeText('{{ $item->url }}')" />
                @can('media.edit')<x-icon-btn icon="edit" :href="route('admin.media.edit', $item)" label="Edit" />@endcan
                @can('media.delete')<form method="POST" action="{{ route('admin.media.destroy', $item) }}" onsubmit="return confirm('Delete?')">@csrf @method('DELETE')<x-icon-btn icon="trash" type="submit" variant="danger" label="Delete" /></form>@endcan
            </div>
        </td>
    </tr>
    @empty
    <tr><td colspan="8" class="px-4 py-12 text-center text-slate-400">
        @if(request()->filled('search') || request()->filled('folder'))
            <p class="font-medium text-slate-500">No media match your filters.</p>
            <div class="mt-4"><x-btn :href="route('admin.media.index')" variant="outline" size="sm">Clear filters</x-btn></div>
        @else
            No media uploaded.
        @endif
    </td></tr>
    @endforelse
</x-table>
<div class="mt-4">{{ $media->links() }}</div>
@endsection
