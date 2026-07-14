@extends('layouts.admin')
@section('title', 'Posts')
@section('content')
<x-page-header title="Posts" subtitle="Blog articles.">
    <x-slot:actions>@can('posts.create')<x-btn :href="route('admin.posts.create')"><x-icon name="plus" class="w-4 h-4" /> New post</x-btn>@endcan</x-slot:actions>
</x-page-header>

<x-search placeholder="Search posts…">
    <x-slot:filters>
        <select name="status" onchange="this.form.submit()" class="rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm px-3.5 py-2.5 brand-ring shadow-sm">
            <option value="">All statuses</option>
            <option value="published" @selected(request('status')==='published')>Published</option>
            <option value="draft" @selected(request('status')==='draft')>Draft</option>
        </select>
    </x-slot:filters>
</x-search>

<x-table :headings="['Title', 'Category', 'Author', 'Status', 'Published', '']">
    @forelse($posts as $post)
    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/60">
        <td class="px-4 py-3 font-medium">{{ $post->title }}</td>
        <td class="px-4 py-3 text-slate-500">{{ $post->category?->name ?? '—' }}</td>
        <td class="px-4 py-3 text-slate-500">{{ $post->author?->name ?? '—' }}</td>
        <td class="px-4 py-3"><x-badge :color="$post->status==='published'?'green':'slate'">{{ $post->status }}</x-badge></td>
        <td class="px-4 py-3 text-slate-500">{{ $post->published_at?->format('M j, Y') ?? '—' }}</td>
        <td class="px-4 py-3">
            <div class="flex items-center justify-end gap-1">
                <x-icon-btn icon="eye" variant="view" :href="route('blog.show', $post->slug)" target="_blank" label="View" />
                @can('posts.edit')<x-icon-btn icon="edit" :href="route('admin.posts.edit', $post)" label="Edit" />@endcan
                @can('posts.delete')<form method="POST" action="{{ route('admin.posts.destroy', $post) }}" onsubmit="return confirm('Delete post?')">@csrf @method('DELETE')<x-icon-btn icon="trash" type="submit" variant="danger" label="Delete" /></form>@endcan
            </div>
        </td>
    </tr>
    @empty<tr><td colspan="6" class="px-4 py-12 text-center text-slate-400">No posts yet.</td></tr>@endforelse
</x-table>
{{ $posts->links() }}
@endsection
