@extends('layouts.admin')
@section('title', 'Categories')
@section('content')
<x-page-header title="Categories" subtitle="Organise posts into categories." />
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2">
        <x-table :headings="['Name', 'Slug', 'Parent', 'Posts', '']">
            @forelse($categories as $cat)
            <tr x-data="{ edit:false }" class="hover:bg-slate-50 dark:hover:bg-slate-800/60">
                <td class="px-4 py-3 font-medium">
                    <span x-show="!edit">{{ $cat->name }}</span>
                    <form x-show="edit" x-cloak method="POST" action="{{ route('admin.categories.update', $cat) }}" class="flex gap-2">@csrf @method('PUT')
                        <input name="name" value="{{ $cat->name }}" class="rounded-lg border border-slate-300 dark:border-slate-600 dark:bg-slate-800 text-sm px-2 py-1">
                        <select name="parent_id" class="rounded-lg border border-slate-300 dark:border-slate-600 dark:bg-slate-800 text-sm px-2 py-1">
                            <option value="">— none —</option>
                            @foreach($parents as $id=>$name)@if($id!==$cat->id)<option value="{{ $id }}" @selected($cat->parent_id===$id)>{{ $name }}</option>@endif @endforeach
                        </select>
                        <x-btn size="sm" type="submit">Save</x-btn>
                    </form>
                </td>
                <td class="px-4 py-3 text-slate-500">{{ $cat->slug }}</td>
                <td class="px-4 py-3 text-slate-500">{{ $cat->parent?->name ?? '—' }}</td>
                <td class="px-4 py-3">{{ $cat->posts_count }}</td>
                <td class="px-4 py-3 text-right whitespace-nowrap">
                    @can('categories.edit')<button x-on:click="edit=!edit" class="text-xs brand-gradient-text font-medium">Edit</button>@endcan
                    @can('categories.delete')<form method="POST" action="{{ route('admin.categories.destroy', $cat) }}" class="inline ml-2" onsubmit="return confirm('Delete?')">@csrf @method('DELETE')<button class="text-xs text-red-500">Del</button></form>@endcan
                </td>
            </tr>
            @empty<tr><td colspan="5" class="px-4 py-10 text-center text-slate-400">No categories yet.</td></tr>@endforelse
        </x-table>
    </div>
    @can('categories.create')
    <x-card title="Add category">
        <form method="POST" action="{{ route('admin.categories.store') }}" class="space-y-4">@csrf
            <x-form.input name="name" label="Name" required />
            <x-form.select name="parent_id" label="Parent" :options="$parents" placeholder="— none —" />
            <x-btn type="submit" class="w-full">Add</x-btn>
        </form>
    </x-card>
    @endcan
</div>
@endsection
