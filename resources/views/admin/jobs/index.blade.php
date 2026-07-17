@extends('layouts.admin')
@section('title', 'Jobs')
@section('content')
<x-page-header title="Jobs" subtitle="Open positions and career listings.">
    <x-slot:actions>@can('jobs.create')<x-btn :href="route('admin.jobs.create')"><x-icon name="plus" class="w-4 h-4" /> New job</x-btn>@endcan</x-slot:actions>
</x-page-header>

<x-search placeholder="Search by title or location…">
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
    ['key' => 'location', 'label' => 'Location', 'sortable' => true],
    ['key' => 'employment_type', 'label' => 'Type', 'sortable' => true],
    ['key' => 'status', 'label' => 'Status', 'sortable' => true],
    ['key' => 'published_at', 'label' => 'Published', 'sortable' => true],
    ['label' => ''],
]">
    <x-slot:toolbar>
        @canany(['jobs.delete', 'jobs.edit'])
        <x-bulk-actions :action="route('admin.jobs.bulk')" :options="[
            'delete' => 'Delete selected',
            'publish' => 'Publish selected',
            'draft' => 'Mark as draft',
        ]" />
        @endcanany
    </x-slot:toolbar>

    @forelse($jobs as $jobListing)
    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/60">
        <x-table-checkbox :id="$jobListing->id" />
        <td class="px-4 py-3 font-medium">{{ $jobListing->title }}</td>
        <td class="px-4 py-3 text-slate-500">{{ $jobListing->location ?? '—' }}</td>
        <td class="px-4 py-3 text-slate-500">{{ str_replace('-', ' ', ucfirst($jobListing->employment_type)) }}</td>
        <td class="px-4 py-3"><x-badge :color="$jobListing->status==='published'?'green':'slate'">{{ $jobListing->status }}</x-badge></td>
        <td class="px-4 py-3 text-slate-500">{{ $jobListing->published_at?->format('M j, Y') ?? '—' }}</td>
        <td class="px-4 py-3">
            <div class="flex items-center justify-end gap-1">
                @can('jobs.edit')<x-icon-btn icon="edit" :href="route('admin.jobs.edit', $jobListing)" label="Edit" />@endcan
                @can('jobs.delete')<form method="POST" action="{{ route('admin.jobs.destroy', $jobListing) }}" onsubmit="return confirm('Delete job listing?')">@csrf @method('DELETE')<x-icon-btn icon="trash" type="submit" variant="danger" label="Delete" /></form>@endcan
            </div>
        </td>
    </tr>
    @empty
    <tr><td colspan="7" class="px-4 py-12 text-center text-slate-400">No job listings yet.</td></tr>
    @endforelse
</x-table>
{{ $jobs->links() }}
@endsection
