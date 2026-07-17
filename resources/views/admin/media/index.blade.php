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

<x-search placeholder="Search files…" />

<x-table bulk :columns="[
    ['key' => null, 'label' => 'Preview', 'sortable' => false],
    ['key' => null, 'label' => 'Filename', 'sortable' => false],
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
        </td>
        <td class="px-4 py-3 text-slate-500 text-xs">{{ $item->mime ?? '—' }}</td>
        <td class="px-4 py-3 text-slate-500">{{ $item->created_at->format('M j, Y') }}</td>
        <td class="px-4 py-3">
            <div class="flex items-center justify-end gap-2">
                <button type="button" onclick="navigator.clipboard.writeText('{{ $item->url }}')" class="text-xs brand-gradient-text">Copy URL</button>
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
