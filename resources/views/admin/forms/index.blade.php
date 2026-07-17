@extends('layouts.admin')
@section('title', 'Forms')
@section('content')
<x-page-header title="Forms" subtitle="Build public forms and review submissions.">
    <x-slot:actions>@can('forms.create')<x-btn :href="route('admin.forms.create')"><x-icon name="plus" class="w-4 h-4" /> New form</x-btn>@endcan</x-slot:actions>
</x-page-header>

<x-search placeholder="Search forms...">
    <x-slot:filters>
        <select name="active" onchange="this.form.submit()" class="rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm px-3.5 py-2.5 brand-ring shadow-sm">
            <option value="">All statuses</option>
            <option value="1" @selected(request('active') === '1')>Active</option>
            <option value="0" @selected(request('active') === '0')>Inactive</option>
        </select>
    </x-slot:filters>
</x-search>

<x-table bulk :columns="[
    ['key' => 'name', 'label' => 'Name', 'sortable' => true],
    ['key' => 'slug', 'label' => 'Slug', 'sortable' => true],
    ['key' => 'is_active', 'label' => 'Status', 'sortable' => true],
    ['label' => 'Submissions'],
    ['key' => 'created_at', 'label' => 'Created', 'sortable' => true],
    ['label' => ''],
]">
    <x-slot:toolbar>
        @canany(['forms.delete', 'forms.edit'])
        <x-bulk-actions :action="route('admin.forms.bulk')" :options="[
            'delete' => 'Delete selected',
            'activate' => 'Activate selected',
            'deactivate' => 'Deactivate selected',
        ]" />
        @endcanany
    </x-slot:toolbar>

    @forelse($forms as $form)
    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/60">
        <x-table-checkbox :id="$form->id" />
        <td class="px-4 py-3 font-medium">{{ $form->name }}</td>
        <td class="px-4 py-3 text-slate-500">/forms/{{ $form->slug }}</td>
        <td class="px-4 py-3"><x-badge :color="$form->is_active ? 'green' : 'slate'">{{ $form->is_active ? 'Active' : 'Inactive' }}</x-badge></td>
        <td class="px-4 py-3 text-slate-500">{{ $form->submissions_count }}</td>
        <td class="px-4 py-3 text-slate-500">{{ $form->created_at->format('M j, Y') }}</td>
        <td class="px-4 py-3">
            <div class="flex items-center justify-end gap-1">
                <x-icon-btn icon="eye" variant="view" :href="route('forms.show', $form->slug)" target="_blank" label="View" />
                <x-icon-btn icon="external" :href="route('admin.forms.submissions.index', $form)" label="Submissions" />
                @can('forms.edit')<x-icon-btn icon="edit" :href="route('admin.forms.edit', $form)" label="Edit" />@endcan
                @can('forms.delete')<form method="POST" action="{{ route('admin.forms.destroy', $form) }}" onsubmit="return confirm('Delete form and its submissions?')">@csrf @method('DELETE')<x-icon-btn icon="trash" type="submit" variant="danger" label="Delete" /></form>@endcan
            </div>
        </td>
    </tr>
    @empty
    <tr><td colspan="7" class="px-4 py-12 text-center text-slate-400">No forms yet.</td></tr>
    @endforelse
</x-table>
{{ $forms->links() }}
@endsection
