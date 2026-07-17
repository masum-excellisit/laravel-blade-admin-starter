@extends('layouts.admin')
@section('title', 'Services')
@section('content')
<x-page-header title="Services" subtitle="Offerings displayed on the public site.">
    <x-slot:actions>@can('services.create')<x-btn :href="route('admin.services.create')"><x-icon name="plus" class="w-4 h-4" /> New service</x-btn>@endcan</x-slot:actions>
</x-page-header>

<x-search placeholder="Search services…">
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
    ['key' => 'status', 'label' => 'Status', 'sortable' => true],
    ['key' => 'sort_order', 'label' => 'Order', 'sortable' => true],
    ['key' => 'created_at', 'label' => 'Created', 'sortable' => true],
    ['label' => ''],
]">
    <x-slot:toolbar>
        @canany(['services.delete', 'services.edit'])
        <x-bulk-actions :action="route('admin.services.bulk')" :options="[
            'delete' => 'Delete selected',
            'publish' => 'Publish selected',
            'draft' => 'Mark as draft',
        ]" />
        @endcanany
    </x-slot:toolbar>

    @forelse($services as $service)
    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/60">
        <x-table-checkbox :id="$service->id" />
        <td class="px-4 py-3 font-medium">{{ $service->title }}</td>
        <td class="px-4 py-3"><x-badge :color="$service->status==='published'?'green':'slate'">{{ $service->status }}</x-badge></td>
        <td class="px-4 py-3 text-slate-500">{{ $service->sort_order }}</td>
        <td class="px-4 py-3 text-slate-500">{{ $service->created_at->format('M j, Y') }}</td>
        <td class="px-4 py-3">
            <div class="flex items-center justify-end gap-1">
                @can('services.edit')<x-icon-btn icon="edit" :href="route('admin.services.edit', $service)" label="Edit" />@endcan
                @can('services.delete')<form method="POST" action="{{ route('admin.services.destroy', $service) }}" onsubmit="return confirm('Delete service?')">@csrf @method('DELETE')<x-icon-btn icon="trash" type="submit" variant="danger" label="Delete" /></form>@endcan
            </div>
        </td>
    </tr>
    @empty
    <tr><td colspan="6" class="px-4 py-12 text-center text-slate-400">No services yet.</td></tr>
    @endforelse
</x-table>
{{ $services->links() }}
@endsection
