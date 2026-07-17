@extends('layouts.admin')
@section('title', 'Form submissions')
@section('content')
<x-page-header :title="$form->name.' submissions'" subtitle="Stored public form entries.">
    <x-slot:actions>
        <x-btn variant="outline" :href="route('admin.forms.index')">Back to forms</x-btn>
    </x-slot:actions>
</x-page-header>

<x-table :columns="[
    ['label' => 'Submitted'],
    ['label' => 'IP address'],
    ['label' => 'Summary'],
    ['label' => ''],
]">
    @forelse($submissions as $submission)
    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/60">
        <td class="px-4 py-3 text-slate-500">{{ $submission->created_at->format('M j, Y g:i a') }}</td>
        <td class="px-4 py-3 text-slate-500">{{ $submission->ip_address ?? '—' }}</td>
        <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ \Illuminate\Support\Str::limit(json_encode($submission->data), 100) }}</td>
        <td class="px-4 py-3">
            <div class="flex items-center justify-end gap-1">
                <x-icon-btn icon="eye" variant="view" :href="route('admin.forms.submissions.show', [$form, $submission])" label="View" />
                @can('forms.delete')<form method="POST" action="{{ route('admin.forms.submissions.destroy', [$form, $submission]) }}" onsubmit="return confirm('Delete submission?')">@csrf @method('DELETE')<x-icon-btn icon="trash" type="submit" variant="danger" label="Delete" /></form>@endcan
            </div>
        </td>
    </tr>
    @empty
    <tr><td colspan="4" class="px-4 py-12 text-center text-slate-400">No submissions yet.</td></tr>
    @endforelse
</x-table>
{{ $submissions->links() }}
@endsection
