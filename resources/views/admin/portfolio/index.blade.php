@extends('layouts.admin')
@section('title', 'Portfolio')
@section('content')
<x-page-header title="Portfolio" subtitle="Case studies and project highlights displayed on the public site.">
    <x-slot:actions>@can('portfolio.create')<x-btn :href="route('admin.portfolio.create')"><x-icon name="plus" class="w-4 h-4" /> New portfolio item</x-btn>@endcan</x-slot:actions>
</x-page-header>

<x-search placeholder="Search portfolio items...">
    <x-slot:filters>
        <select name="status" onchange="this.form.submit()" class="rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm px-3.5 py-2.5 brand-ring shadow-sm">
            <option value="">All statuses</option>
            <option value="published" @selected(request('status')==='published')>Published</option>
            <option value="draft" @selected(request('status')==='draft')>Draft</option>
        </select>
    </x-slot:filters>
</x-search>

<x-table bulk :columns="[
    ['key' => 'title', 'label' => 'Title', 'sortable' => true],
    ['key' => 'client', 'label' => 'Client', 'sortable' => true],
    ['key' => 'sort_order', 'label' => 'Order', 'sortable' => true],
    ['key' => 'status', 'label' => 'Status', 'sortable' => true],
    ['key' => 'published_at', 'label' => 'Published', 'sortable' => true],
    ['label' => ''],
]">
    <x-slot:toolbar>
        @canany(['portfolio.delete', 'portfolio.edit'])
        <x-bulk-actions :action="route('admin.portfolio.bulk')" :options="[
            'delete' => 'Delete selected',
            'publish' => 'Publish selected',
            'draft' => 'Mark as draft',
        ]" />
        @endcanany
    </x-slot:toolbar>

    @forelse($portfolioItems as $portfolioItem)
    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/60">
        <x-table-checkbox :id="$portfolioItem->id" />
        <td class="px-4 py-3">
            <p class="font-medium">{{ $portfolioItem->title }}</p>
            <p class="text-xs text-slate-400">{{ $portfolioItem->slug }}</p>
        </td>
        <td class="px-4 py-3 text-slate-500">{{ $portfolioItem->client ?? '-' }}</td>
        <td class="px-4 py-3 text-slate-500">{{ $portfolioItem->sort_order }}</td>
        <td class="px-4 py-3"><x-badge :color="$portfolioItem->status==='published'?'green':'slate'">{{ $portfolioItem->status }}</x-badge></td>
        <td class="px-4 py-3 text-slate-500">{{ $portfolioItem->published_at?->format('M j, Y') ?? '-' }}</td>
        <td class="px-4 py-3">
            <div class="flex items-center justify-end gap-1">
                @can('portfolio.edit')<x-icon-btn icon="edit" :href="route('admin.portfolio.edit', $portfolioItem)" label="Edit" />@endcan
                @can('portfolio.delete')<form method="POST" action="{{ route('admin.portfolio.destroy', $portfolioItem) }}" onsubmit="return confirm('Delete portfolio item?')">@csrf @method('DELETE')<x-icon-btn icon="trash" type="submit" variant="danger" label="Delete" /></form>@endcan
            </div>
        </td>
    </tr>
    @empty
    <tr><td colspan="7" class="px-4 py-12 text-center text-slate-400">No portfolio items yet.</td></tr>
    @endforelse
</x-table>
{{ $portfolioItems->links() }}
@endsection
