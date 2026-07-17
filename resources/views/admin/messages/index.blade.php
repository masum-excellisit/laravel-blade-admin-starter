@extends('layouts.admin')
@section('title', 'Messages')
@section('content')
<x-page-header title="Contact messages" subtitle="Submissions from the public contact form." />

<x-search placeholder="Search messages…" />

<x-table bulk :columns="[
    ['key' => 'name', 'label' => 'Name', 'sortable' => true],
    ['key' => 'email', 'label' => 'Email', 'sortable' => true],
    ['key' => null, 'label' => 'Subject', 'sortable' => false],
    ['key' => 'created_at', 'label' => 'Received', 'sortable' => true],
    ['key' => null, 'label' => '', 'sortable' => false],
]">
    <x-slot:toolbar>
        <x-bulk-actions :action="route('admin.messages.bulk')" :options="['delete' => 'Delete selected']" />
    </x-slot:toolbar>
    @forelse($messages as $msg)
    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/60 {{ $msg->read ? '' : 'bg-primary-soft/40 dark:bg-slate-800/40' }}">
        <x-table-checkbox :id="$msg->id" />
        <td class="px-4 py-3 {{ $msg->read ? '' : 'font-semibold' }}">{{ $msg->name }}</td>
        <td class="px-4 py-3 text-slate-500">{{ $msg->email }}</td>
        <td class="px-4 py-3 {{ $msg->read ? '' : 'font-semibold' }}">{{ $msg->subject ?? '—' }} @unless($msg->read)<x-badge color="indigo">new</x-badge>@endunless</td>
        <td class="px-4 py-3 text-slate-500 font-normal">{{ $msg->created_at->diffForHumans() }}</td>
        <td class="px-4 py-3">
            <div class="flex items-center justify-end gap-1">
                <x-icon-btn icon="eye" variant="view" :href="route('admin.messages.show', $msg)" label="Read" />
                @can('messages.delete')<form method="POST" action="{{ route('admin.messages.destroy', $msg) }}" onsubmit="return confirm('Delete?')">@csrf @method('DELETE')<x-icon-btn icon="trash" type="submit" variant="danger" label="Delete" /></form>@endcan
            </div>
        </td>
    </tr>
    @empty<tr><td colspan="6" class="px-4 py-12 text-center text-slate-400">No messages.</td></tr>@endforelse
</x-table>
{{ $messages->links() }}
@endsection
