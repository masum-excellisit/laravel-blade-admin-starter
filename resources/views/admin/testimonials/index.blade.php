@extends('layouts.admin')
@section('title', 'Testimonials')
@section('content')
<x-page-header title="Testimonials" subtitle="Customer quotes and reviews.">
    <x-slot:actions>@can('testimonials.create')<x-btn :href="route('admin.testimonials.create')"><x-icon name="plus" class="w-4 h-4" /> New testimonial</x-btn>@endcan</x-slot:actions>
</x-page-header>

<x-search placeholder="Search by author or quote…">
    <x-slot:filters>
        <select name="status" onchange="this.form.submit()" class="rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm px-3.5 py-2.5 brand-ring shadow-sm">
            <option value="">All statuses</option>
            <option value="published" @selected(request('status')==='published')>Published</option>
            <option value="draft" @selected(request('status')==='draft')>Draft</option>
        </select>
    </x-slot:filters>
</x-search>

<x-table bulk :columns="[
    ['key' => 'author_name', 'label' => 'Author', 'sortable' => true],
    ['key' => 'rating', 'label' => 'Rating', 'sortable' => true],
    ['key' => 'sort_order', 'label' => 'Order', 'sortable' => true],
    ['key' => 'status', 'label' => 'Status', 'sortable' => true],
    ['key' => 'created_at', 'label' => 'Created', 'sortable' => true],
    ['label' => ''],
]">
    <x-slot:toolbar>
        @canany(['testimonials.delete', 'testimonials.edit'])
        <x-bulk-actions :action="route('admin.testimonials.bulk')" :options="[
            'delete' => 'Delete selected',
            'publish' => 'Publish selected',
            'draft' => 'Mark as draft',
        ]" />
        @endcanany
    </x-slot:toolbar>

    @forelse($testimonials as $testimonial)
    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/60">
        <x-table-checkbox :id="$testimonial->id" />
        <td class="px-4 py-3">
            <p class="font-medium">{{ $testimonial->author_name }}</p>
            @if($testimonial->author_title)<p class="text-xs text-slate-400">{{ $testimonial->author_title }}</p>@endif
        </td>
        <td class="px-4 py-3 text-slate-500">{{ $testimonial->rating }}/5</td>
        <td class="px-4 py-3 text-slate-500">{{ $testimonial->sort_order }}</td>
        <td class="px-4 py-3"><x-badge :color="$testimonial->status==='published'?'green':'slate'">{{ $testimonial->status }}</x-badge></td>
        <td class="px-4 py-3 text-slate-500">{{ $testimonial->created_at->format('M j, Y') }}</td>
        <td class="px-4 py-3">
            <div class="flex items-center justify-end gap-1">
                @can('testimonials.edit')<x-icon-btn icon="edit" :href="route('admin.testimonials.edit', $testimonial)" label="Edit" />@endcan
                @can('testimonials.delete')<form method="POST" action="{{ route('admin.testimonials.destroy', $testimonial) }}" onsubmit="return confirm('Delete testimonial?')">@csrf @method('DELETE')<x-icon-btn icon="trash" type="submit" variant="danger" label="Delete" /></form>@endcan
            </div>
        </td>
    </tr>
    @empty
    <tr><td colspan="7" class="px-4 py-12 text-center text-slate-400">No testimonials yet.</td></tr>
    @endforelse
</x-table>
{{ $testimonials->links() }}
@endsection
