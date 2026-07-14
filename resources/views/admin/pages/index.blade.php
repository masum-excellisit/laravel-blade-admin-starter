@extends('layouts.admin')
@section('title', 'Pages')
@section('content')
<x-page-header title="Pages" subtitle="Content pages rendered on the public site.">
    <x-slot:actions>@can('pages.create')<x-btn :href="route('admin.pages.create')"><x-icon name="plus" class="w-4 h-4" /> New page</x-btn>@endcan</x-slot:actions>
</x-page-header>

<x-search placeholder="Search pages…">
    <x-slot:filters>
        <select name="status" onchange="this.form.submit()" class="rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm px-3.5 py-2.5 brand-ring shadow-sm">
            <option value="">All statuses</option>
            <option value="published" @selected(request('status')==='published')>Published</option>
            <option value="draft" @selected(request('status')==='draft')>Draft</option>
        </select>
    </x-slot:filters>
</x-search>

<x-table :headings="['Title', 'Slug', 'Template', 'Status', '']">
    @forelse($pages as $page)
    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/60">
        <td class="px-4 py-3 font-medium">{{ $page->title }}</td>
        <td class="px-4 py-3 text-slate-500">/{{ $page->slug }}</td>
        <td class="px-4 py-3 text-slate-500">{{ $page->template }}</td>
        <td class="px-4 py-3"><x-badge :color="$page->status==='published'?'green':'slate'">{{ $page->status }}</x-badge></td>
        <td class="px-4 py-3">
            <div class="flex items-center justify-end gap-1">
                <x-icon-btn icon="eye" variant="view" :href="url('/'.$page->slug)" target="_blank" label="View" />
                @can('pages.edit')<x-icon-btn icon="edit" :href="route('admin.pages.edit', $page)" label="Edit" />@endcan
                @can('pages.delete')<form method="POST" action="{{ route('admin.pages.destroy', $page) }}" onsubmit="return confirm('Delete page?')">@csrf @method('DELETE')<x-icon-btn icon="trash" type="submit" variant="danger" label="Delete" /></form>@endcan
            </div>
        </td>
    </tr>
    @empty<tr><td colspan="5" class="px-4 py-12 text-center text-slate-400">No pages yet.</td></tr>@endforelse
</x-table>
{{ $pages->links() }}
@endsection
