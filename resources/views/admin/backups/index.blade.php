@extends('layouts.admin')
@section('title', 'Backups')
@section('content')
<x-page-header title="Backups" subtitle="Export CMS content for safekeeping or migration." />

<x-card title="Create an export">
    <div class="prose prose-sm max-w-none dark:prose-invert">
        <p>
            The export includes pages, posts, menus, settings, FAQs, team members, portfolio items,
            content blocks, forms and fields, redirects, and CMS content. User passwords and form
            submissions are not included.
        </p>
        <p>
            When ZIP support is available, the download contains a <code>content.json</code> file and,
            when the public media folder is below the export size limit, media files from public storage.
            If ZIP support is unavailable, the export falls back to a JSON download.
        </p>
    </div>

    @can('backups.create')
        <form method="POST" action="{{ route('admin.backups.download') }}" class="mt-6">
            @csrf
            <x-btn type="submit">Download export</x-btn>
        </form>
    @else
        <p class="mt-6 text-sm text-slate-500">You can view backup information but do not have permission to create exports.</p>
    @endcan
</x-card>
@endsection
