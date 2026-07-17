@extends('layouts.admin')
@section('title', 'Submission')
@section('content')
<x-page-header :title="$form->name.' submission'" :subtitle="$submission->created_at->format('F j, Y g:i a')">
    <x-slot:actions>
        <x-btn variant="outline" :href="route('admin.forms.submissions.index', $form)">Back</x-btn>
        @can('forms.delete')
        <form method="POST" action="{{ route('admin.forms.submissions.destroy', [$form, $submission]) }}" onsubmit="return confirm('Delete submission?')">@csrf @method('DELETE')<x-btn variant="danger" type="submit">Delete</x-btn></form>
        @endcan
    </x-slot:actions>
</x-page-header>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2">
        <x-card title="Submission data">
            <dl class="divide-y divide-slate-100 dark:divide-slate-700">
                @foreach($submission->data as $key => $value)
                <div class="py-3 grid grid-cols-1 sm:grid-cols-3 gap-2 text-sm">
                    <dt class="font-medium text-slate-500">{{ \Illuminate\Support\Str::headline($key) }}</dt>
                    <dd class="sm:col-span-2 text-slate-800 dark:text-slate-100 whitespace-pre-line">
                        @if(is_bool($value))
                            {{ $value ? 'Yes' : 'No' }}
                        @elseif(is_array($value))
                            {{ implode(', ', $value) }}
                        @else
                            {{ $value ?: '—' }}
                        @endif
                    </dd>
                </div>
                @endforeach
            </dl>
        </x-card>
    </div>
    <div>
        <x-card title="Details">
            <dl class="space-y-3 text-sm">
                <div><dt class="text-slate-400">Form</dt><dd class="font-medium">{{ $form->name }}</dd></div>
                <div><dt class="text-slate-400">IP address</dt><dd class="font-medium">{{ $submission->ip_address ?? '—' }}</dd></div>
                <div><dt class="text-slate-400">Submitted</dt><dd class="font-medium">{{ $submission->created_at->format('F j, Y g:i a') }}</dd></div>
            </dl>
        </x-card>
    </div>
</div>
@endsection
