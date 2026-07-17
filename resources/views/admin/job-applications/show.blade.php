@extends('layouts.admin')
@section('title', 'Application')
@section('content')
<x-page-header :title="$application->name" :subtitle="'Application for '.($application->jobListing?->title ?? 'Unknown position')">
    <x-slot:actions>
        <x-btn variant="outline" :href="route('admin.job-applications.index')">Back</x-btn>
        @can('job-applications.delete')
        <form method="POST" action="{{ route('admin.job-applications.destroy', $application) }}" onsubmit="return confirm('Delete application?')">@csrf @method('DELETE')<x-btn variant="danger" type="submit">Delete</x-btn></form>
        @endcan
    </x-slot:actions>
</x-page-header>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">
        <x-card title="Applicant details">
            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                <div><dt class="text-slate-400">Email</dt><dd class="font-medium mt-0.5"><a href="mailto:{{ $application->email }}" class="text-primary hover:underline">{{ $application->email }}</a></dd></div>
                <div><dt class="text-slate-400">Phone</dt><dd class="font-medium mt-0.5">{{ $application->phone ?? '—' }}</dd></div>
                <div><dt class="text-slate-400">Submitted</dt><dd class="font-medium mt-0.5">{{ $application->created_at->format('F j, Y g:i a') }}</dd></div>
                <div><dt class="text-slate-400">Job</dt><dd class="font-medium mt-0.5">{{ $application->jobListing?->title ?? '—' }}</dd></div>
            </dl>
        </x-card>

        @if($application->cover_letter)
        <x-card title="Cover letter">
            <p class="whitespace-pre-line text-slate-700 dark:text-slate-200">{{ $application->cover_letter }}</p>
        </x-card>
        @endif

        @if($application->resume_path)
        <x-card title="Resume">
            <x-btn :href="$application->resumeUrl()" target="_blank"><x-icon name="external" class="w-4 h-4" /> Download resume</x-btn>
        </x-card>
        @endif
    </div>

    <div>
        @can('job-applications.edit')
        <x-card title="Status">
            <form method="POST" action="{{ route('admin.job-applications.update', $application) }}" class="space-y-4">
                @csrf
                @method('PUT')
                <x-form.select name="status" label="Application status" :options="[
                    'new' => 'New',
                    'reviewed' => 'Reviewed',
                    'shortlisted' => 'Shortlisted',
                    'rejected' => 'Rejected',
                    'hired' => 'Hired',
                ]" :selected="$application->status" />
                <x-btn type="submit" class="w-full">Update status</x-btn>
            </form>
        </x-card>
        @else
        <x-card title="Status">
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
            <x-badge :color="$statusColor" class="text-sm">{{ ucfirst($application->status) }}</x-badge>
        </x-card>
        @endcan
    </div>
</div>
@endsection
