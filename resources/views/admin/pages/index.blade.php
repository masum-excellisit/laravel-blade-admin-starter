@extends('layouts.admin')
@section('title', 'Pages')
@section('content')
<x-page-header title="Pages" subtitle="Content pages rendered on the public site.">
    <x-slot:actions>@can('pages.create')<x-btn :href="route('admin.pages.create')">+ New page</x-btn>@endcan</x-slot:actions>
</x-page-header>
<x-table :headings="['Title', 'Slug', 'Template', 'Status', '']">
    @forelse($pages as $page)
    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/60">
        <td class="px-4 py-3 font-medium">{{ $page->title }}</td>
        <td class="px-4 py-3 text-slate-500">/{{ $page->slug }}</td>
        <td class="px-4 py-3 text-slate-500">{{ $page->template }}</td>
        <td class="px-4 py-3"><x-badge :color="$page->status==='published'?'green':'slate'">{{ $page->status }}</x-badge></td>
        <td class="px-4 py-3 text-right whitespace-nowrap">
            <x-btn size="sm" variant="ghost" :href="url('/'.$page->slug)" target="_blank">View</x-btn>
            @can('pages.edit')<x-btn size="sm" variant="ghost" :href="route('admin.pages.edit', $page)">Edit</x-btn>@endcan
            @can('pages.delete')<form method="POST" action="{{ route('admin.pages.destroy', $page) }}" class="inline" onsubmit="return confirm('Delete page?')">@csrf @method('DELETE')<x-btn size="sm" variant="ghost" type="submit" class="!text-red-600">Delete</x-btn></form>@endcan
        </td>
    </tr>
    @empty<tr><td colspan="5" class="px-4 py-10 text-center text-slate-400">No pages yet.</td></tr>@endforelse
</x-table>
<div class="mt-4">{{ $pages->links() }}</div>
@endsection
