@extends('layouts.admin')
@section('title', 'Job Applications')
@section('content')
<x-page-header title="Job applications" subtitle="Submissions from the public careers page." />

<x-search placeholder="Search by name or email…">
    <x-slot:filters>
        <select name="status" onchange="this.form.submit()" class="rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm px-3.5 py-2.5 brand-ring shadow-sm">
            <option value="">All statuses</option>
            @foreach(['new' => 'New', 'reviewed' => 'Reviewed', 'shortlisted' => 'Shortlisted', 'rejected' => 'Rejected', 'hired' => 'Hired'] as $value => $label)
                <option value="{{ $value }}" @selected(request('status')===$value)>{{ $label }}</option>
            @endforeach
        </select>
    </x-slot:filters>
</x-search>

<x-table bulk :columns="[
    ['key' => 'name', 'label' => 'Applicant', 'sortable' => true],
    ['key' => 'email', 'label' => 'Email', 'sortable' => true],
    ['label' => 'Job'],
    ['key' => 'status', 'label' => 'Status', 'sortable' => true],
    ['key' => 'created_at', 'label' => 'Submitted', 'sortable' => true],
    ['label' => ''],
]">
    <x-slot:toolbar>
        @canany(['job-applications.delete', 'job-applications.edit'])
        <x-bulk-actions :action="route('admin.job-applications.bulk')" :options="[
            'delete' => 'Delete selected',
            'reviewed' => 'Mark as reviewed',
            'shortlisted' => 'Mark as shortlisted',
            'rejected' => 'Mark as rejected',
        ]" />
        @endcanany
    </x-slot:toolbar>

    @forelse($applications as $application)
    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/60 {{ $application->status === 'new' ? 'bg-primary-soft/40 dark:bg-slate-800/40' : '' }}">
        <x-table-checkbox :id="$application->id" />
        <td class="px-4 py-3 {{ $application->status === 'new' ? 'font-semibold' : 'font-medium' }}">{{ $application->name }}</td>
        <td class="px-4 py-3 text-slate-500">{{ $application->email }}</td>
        <td class="px-4 py-3 text-slate-500">{{ $application->jobListing?->title ?? '—' }}</td>
        <td class="px-4 py-3">
            @php
                $statusColor = match($application->status) {
                    'new' => 'indigo',
                    'reviewed' => 'slate',
                    'shortlisted' => 'green',
                    'rejected' => 'red',
                    'hired' => 'green',
                    default => 'slate',
                };
            @endphp
            <x-badge :color="$statusColor">{{ $application->status }}</x-badge>
        </td>
        <td class="px-4 py-3 text-slate-500">{{ $application->created_at->format('M j, Y') }}</td>
        <td class="px-4 py-3">
            <div class="flex items-center justify-end gap-1">
                <x-icon-btn icon="eye" variant="view" :href="route('admin.job-applications.show', $application)" label="View" />
                @can('job-applications.delete')
                <form method="POST" action="{{ route('admin.job-applications.destroy', $application) }}" onsubmit="return confirm('Delete application?')">@csrf @method('DELETE')<x-icon-btn icon="trash" type="submit" variant="danger" label="Delete" /></form>
                @endcan
            </div>
        </td>
    </tr>
    @empty
    <tr><td colspan="7" class="px-4 py-12 text-center text-slate-400">No applications yet.</td></tr>
    @endforelse
</x-table>
{{ $applications->links() }}
@endsection
