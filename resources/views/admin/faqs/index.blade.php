@extends('layouts.admin')
@section('title', 'FAQs')
@section('content')
<x-page-header title="FAQs" subtitle="Frequently asked questions displayed on the public site.">
    <x-slot:actions>@can('faqs.create')<x-btn :href="route('admin.faqs.create')"><x-icon name="plus" class="w-4 h-4" /> New FAQ</x-btn>@endcan</x-slot:actions>
</x-page-header>

<x-search placeholder="Search questions or answers...">
    <x-slot:filters>
        <select name="status" onchange="this.form.submit()" class="rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm px-3.5 py-2.5 brand-ring shadow-sm">
            <option value="">All statuses</option>
            <option value="published" @selected(request('status')==='published')>Published</option>
            <option value="draft" @selected(request('status')==='draft')>Draft</option>
        </select>
    </x-slot:filters>
</x-search>

<x-table bulk :columns="[
    ['key' => 'question', 'label' => 'Question', 'sortable' => true],
    ['key' => 'category', 'label' => 'Category', 'sortable' => true],
    ['key' => 'sort_order', 'label' => 'Order', 'sortable' => true],
    ['key' => 'status', 'label' => 'Status', 'sortable' => true],
    ['key' => 'created_at', 'label' => 'Created', 'sortable' => true],
    ['label' => ''],
]">
    <x-slot:toolbar>
        @canany(['faqs.delete', 'faqs.edit'])
        <x-bulk-actions :action="route('admin.faqs.bulk')" :options="[
            'delete' => 'Delete selected',
            'publish' => 'Publish selected',
            'draft' => 'Mark as draft',
        ]" />
        @endcanany
    </x-slot:toolbar>

    @forelse($faqs as $faq)
    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/60">
        <x-table-checkbox :id="$faq->id" />
        <td class="px-4 py-3 font-medium">{{ $faq->question }}</td>
        <td class="px-4 py-3 text-slate-500">{{ $faq->category ?? '-' }}</td>
        <td class="px-4 py-3 text-slate-500">{{ $faq->sort_order }}</td>
        <td class="px-4 py-3"><x-badge :color="$faq->status==='published'?'green':'slate'">{{ $faq->status }}</x-badge></td>
        <td class="px-4 py-3 text-slate-500">{{ $faq->created_at->format('M j, Y') }}</td>
        <td class="px-4 py-3">
            <div class="flex items-center justify-end gap-1">
                @can('faqs.edit')<x-icon-btn icon="edit" :href="route('admin.faqs.edit', $faq)" label="Edit" />@endcan
                @can('faqs.delete')<form method="POST" action="{{ route('admin.faqs.destroy', $faq) }}" onsubmit="return confirm('Delete FAQ?')">@csrf @method('DELETE')<x-icon-btn icon="trash" type="submit" variant="danger" label="Delete" /></form>@endcan
            </div>
        </td>
    </tr>
    @empty
    <tr><td colspan="7" class="px-4 py-12 text-center text-slate-400">No FAQs yet.</td></tr>
    @endforelse
</x-table>
{{ $faqs->links() }}
@endsection
