@extends('layouts.admin')
@section('title', 'Media')
@section('content')
<x-page-header title="Media library" subtitle="Uploaded images and files." />

@can('media.create')
<x-card class="mb-6">
    <form method="POST" action="{{ route('admin.media.store') }}" enctype="multipart/form-data" class="grid grid-cols-1 lg:grid-cols-5 gap-3 items-end">@csrf
        <div class="lg:col-span-2">
            <x-form.label for="files">Files</x-form.label>
            <input type="file" name="files[]" id="files" multiple class="w-full text-sm">
            @error('files.*')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
        </div>
        <x-form.input name="folder" label="Folder" placeholder="Optional folder" />
        <x-form.input name="alt_text" label="Alt text" placeholder="Optional alt text" />
        <x-form.input name="tags" label="Tags" placeholder="Comma-separated tags" />
        <x-btn type="submit">Upload</x-btn>
    </form>
</x-card>
@endcan

<div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4">
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
        <form method="POST" action="{{ route('admin.media.cleanup') }}" onsubmit="return confirm('Delete all unreferenced media records and files?')" class="mb-5">
            @csrf
            <x-btn type="submit" variant="danger">Cleanup unused</x-btn>
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
            <div class="flex items-center justify-end gap-2">
                <button type="button" onclick="navigator.clipboard.writeText('{{ $item->url }}')" class="text-xs brand-gradient-text">Copy URL</button>
                @can('media.edit')<a href="{{ route('admin.media.edit', $item) }}" class="text-xs brand-gradient-text">Edit</a>@endcan
                @can('media.delete')<form method="POST" action="{{ route('admin.media.destroy', $item) }}" onsubmit="return confirm('Delete?')">@csrf @method('DELETE')<button class="text-xs text-red-500">Delete</button></form>@endcan
            </div>
        </td>
    </tr>
    @empty
    <tr><td colspan="6" class="px-4 py-12 text-center text-slate-400">No media uploaded.</td></tr>
    @endforelse
</x-table>
<div class="mt-4">{{ $media->links() }}</div>
@endsection
