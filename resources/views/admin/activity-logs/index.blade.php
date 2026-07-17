@extends('layouts.admin')
@section('title', 'Activity Log')
@section('content')
<x-page-header title="Activity Log" subtitle="Read-only audit trail for admin and site activity." />

<x-search placeholder="Search activity..." />

<x-table bulk :columns="[
    ['key' => 'action', 'label' => 'Action', 'sortable' => true],
    ['key' => null, 'label' => 'Description', 'sortable' => false],
    ['key' => 'subject_type', 'label' => 'Subject', 'sortable' => true],
    ['key' => null, 'label' => 'User', 'sortable' => false],
    ['key' => 'ip_address', 'label' => 'IP', 'sortable' => true],
    ['key' => 'created_at', 'label' => 'Time', 'sortable' => true],
    ['key' => null, 'label' => '', 'sortable' => false],
]">
    <x-slot:toolbar>
        @can('activity-logs.delete')
        <x-bulk-actions :action="route('admin.activity-logs.bulk')" :options="['delete' => 'Delete selected']" />
        @endcan
    </x-slot:toolbar>

    @forelse($logs as $log)
    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/60">
        <x-table-checkbox :id="$log->id" />
        <td class="px-4 py-3">
            <x-badge color="slate">{{ $log->action }}</x-badge>
        </td>
        <td class="px-4 py-3">
            <div class="font-medium">{{ $log->description ?: '—' }}</div>
            @if(! empty($log->properties))
                <div class="mt-1 text-xs text-slate-400 max-w-md truncate">{{ json_encode($log->properties) }}</div>
            @endif
        </td>
        <td class="px-4 py-3 text-slate-500">
            @if($log->subject_type)
                {{ class_basename($log->subject_type) }} #{{ $log->subject_id }}
            @else
                —
            @endif
        </td>
        <td class="px-4 py-3 text-slate-500">
            {{ $log->user?->name ?? 'System' }}
            @if($log->user?->email)
                <div class="text-xs text-slate-400">{{ $log->user->email }}</div>
            @endif
        </td>
        <td class="px-4 py-3 text-slate-500">{{ $log->ip_address ?: '—' }}</td>
        <td class="px-4 py-3 text-slate-500">{{ $log->created_at->diffForHumans() }}</td>
        <td class="px-4 py-3">
            <div class="flex items-center justify-end gap-1">
                @can('activity-logs.delete')
                    <form method="POST" action="{{ route('admin.activity-logs.destroy', $log) }}" onsubmit="return confirm('Delete activity log?')">
                        @csrf
                        @method('DELETE')
                        <x-icon-btn icon="trash" type="submit" variant="danger" label="Delete" />
                    </form>
                @endcan
            </div>
        </td>
    </tr>
    @empty
    <tr><td colspan="8" class="px-4 py-12 text-center text-slate-400">No activity logged yet.</td></tr>
    @endforelse
</x-table>

{{ $logs->links() }}
@endsection
