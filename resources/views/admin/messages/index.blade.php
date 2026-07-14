@extends('layouts.admin')
@section('title', 'Messages')
@section('content')
<x-page-header title="Contact messages" subtitle="Submissions from the public contact form." />
<x-table :headings="['From', 'Subject', 'Received', '']">
    @forelse($messages as $msg)
    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/60 {{ $msg->read ? '' : 'font-semibold' }}">
        <td class="px-4 py-3">{{ $msg->name }}<span class="block text-xs text-slate-400 font-normal">{{ $msg->email }}</span></td>
        <td class="px-4 py-3">{{ $msg->subject ?? '—' }} @unless($msg->read)<x-badge color="indigo">new</x-badge>@endunless</td>
        <td class="px-4 py-3 text-slate-500 font-normal">{{ $msg->created_at->diffForHumans() }}</td>
        <td class="px-4 py-3 text-right"><x-btn size="sm" variant="ghost" :href="route('admin.messages.show', $msg)">Read</x-btn></td>
    </tr>
    @empty<tr><td colspan="4" class="px-4 py-10 text-center text-slate-400">No messages.</td></tr>@endforelse
</x-table>
<div class="mt-4">{{ $messages->links() }}</div>
@endsection
