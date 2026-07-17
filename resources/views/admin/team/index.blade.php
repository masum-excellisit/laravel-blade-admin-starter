@extends('layouts.admin')
@section('title', 'Team')
@section('content')
<x-page-header title="Team" subtitle="Team members displayed on the public site.">
    <x-slot:actions>@can('team.create')<x-btn :href="route('admin.team.create')"><x-icon name="plus" class="w-4 h-4" /> New team member</x-btn>@endcan</x-slot:actions>
</x-page-header>

<x-search placeholder="Search team members...">
    <x-slot:filters>
        <select name="status" onchange="this.form.submit()" class="rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm px-3.5 py-2.5 brand-ring shadow-sm">
            <option value="">All statuses</option>
            <option value="published" @selected(request('status')==='published')>Published</option>
            <option value="draft" @selected(request('status')==='draft')>Draft</option>
        </select>
    </x-slot:filters>
</x-search>

<x-table bulk :columns="[
    ['key' => 'name', 'label' => 'Name', 'sortable' => true],
    ['key' => 'role_title', 'label' => 'Role', 'sortable' => true],
    ['key' => 'sort_order', 'label' => 'Order', 'sortable' => true],
    ['key' => 'status', 'label' => 'Status', 'sortable' => true],
    ['key' => 'created_at', 'label' => 'Created', 'sortable' => true],
    ['label' => ''],
]">
    <x-slot:toolbar>
        @canany(['team.delete', 'team.edit'])
        <x-bulk-actions :action="route('admin.team.bulk')" :options="[
            'delete' => 'Delete selected',
            'publish' => 'Publish selected',
            'draft' => 'Mark as draft',
        ]" />
        @endcanany
    </x-slot:toolbar>

    @forelse($teamMembers as $teamMember)
    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/60">
        <x-table-checkbox :id="$teamMember->id" />
        <td class="px-4 py-3">
            <p class="font-medium">{{ $teamMember->name }}</p>
            @if($teamMember->email)<p class="text-xs text-slate-400">{{ $teamMember->email }}</p>@endif
        </td>
        <td class="px-4 py-3 text-slate-500">{{ $teamMember->role_title ?? '-' }}</td>
        <td class="px-4 py-3 text-slate-500">{{ $teamMember->sort_order }}</td>
        <td class="px-4 py-3"><x-badge :color="$teamMember->status==='published'?'green':'slate'">{{ $teamMember->status }}</x-badge></td>
        <td class="px-4 py-3 text-slate-500">{{ $teamMember->created_at->format('M j, Y') }}</td>
        <td class="px-4 py-3">
            <div class="flex items-center justify-end gap-1">
                @can('team.edit')<x-icon-btn icon="edit" :href="route('admin.team.edit', $teamMember)" label="Edit" />@endcan
                @can('team.delete')<form method="POST" action="{{ route('admin.team.destroy', $teamMember) }}" onsubmit="return confirm('Delete team member?')">@csrf @method('DELETE')<x-icon-btn icon="trash" type="submit" variant="danger" label="Delete" /></form>@endcan
            </div>
        </td>
    </tr>
    @empty
    <tr><td colspan="7" class="px-4 py-12 text-center text-slate-400">No team members yet.</td></tr>
    @endforelse
</x-table>
{{ $teamMembers->links() }}
@endsection
