@extends('layouts.admin')
@section('title', 'Posts')
@section('content')
<x-page-header title="Posts" subtitle="Blog articles.">
    <x-slot:actions>@can('posts.create')<x-btn :href="route('admin.posts.create')">+ New post</x-btn>@endcan</x-slot:actions>
</x-page-header>
<x-table :headings="['Title', 'Category', 'Author', 'Status', 'Published', '']">
    @forelse($posts as $post)
    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/60">
        <td class="px-4 py-3 font-medium">{{ $post->title }}</td>
        <td class="px-4 py-3 text-slate-500">{{ $post->category?->name ?? '—' }}</td>
        <td class="px-4 py-3 text-slate-500">{{ $post->author?->name ?? '—' }}</td>
        <td class="px-4 py-3"><x-badge :color="$post->status==='published'?'green':'slate'">{{ $post->status }}</x-badge></td>
        <td class="px-4 py-3 text-slate-500">{{ $post->published_at?->format('M j, Y') ?? '—' }}</td>
        <td class="px-4 py-3 text-right whitespace-nowrap">
            @can('posts.edit')<x-btn size="sm" variant="ghost" :href="route('admin.posts.edit', $post)">Edit</x-btn>@endcan
            @can('posts.delete')<form method="POST" action="{{ route('admin.posts.destroy', $post) }}" class="inline" onsubmit="return confirm('Delete post?')">@csrf @method('DELETE')<x-btn size="sm" variant="ghost" type="submit" class="!text-red-600">Delete</x-btn></form>@endcan
        </td>
    </tr>
    @empty<tr><td colspan="6" class="px-4 py-10 text-center text-slate-400">No posts yet.</td></tr>@endforelse
</x-table>
<div class="mt-4">{{ $posts->links() }}</div>
@endsection
