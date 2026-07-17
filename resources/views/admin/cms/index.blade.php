@extends('layouts.admin')
@section('title', 'CMS Pages')
@section('content')
<x-page-header title="CMS Management" subtitle="Edit content for fixed public pages." />

<x-table :columns="[
    ['label' => 'Page'],
    ['label' => 'Key'],
    ['label' => ''],
]">
    @forelse($pages as $page)
    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/60">
        <td class="px-4 py-3 font-medium">{{ $page['title'] }}</td>
        <td class="px-4 py-3 text-slate-500">{{ $page['key'] }}</td>
        <td class="px-4 py-3">
            <div class="flex items-center justify-end gap-1">
                @can('cms.edit')
                    <x-icon-btn icon="edit" :href="route('admin.cms.edit', $page['key'])" label="Edit" />
                @else
                    <x-icon-btn icon="eye" variant="view" :href="route('admin.cms.edit', $page['key'])" label="View" />
                @endcan
            </div>
        </td>
    </tr>
    @empty
    <tr><td colspan="3" class="px-4 py-12 text-center text-slate-400">No CMS pages configured.</td></tr>
    @endforelse
</x-table>
@endsection
