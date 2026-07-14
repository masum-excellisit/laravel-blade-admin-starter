@extends('layouts.admin')
@section('title', 'Users')
@section('content')
<x-page-header title="Users" subtitle="Customer accounts (front-end users).">
    <x-slot:actions>
        @can('customers.create')<x-btn :href="route('admin.customers.create')"><x-icon name="plus" class="w-4 h-4" /> New user</x-btn>@endcan
    </x-slot:actions>
</x-page-header>

<x-search placeholder="Search by name or email…">
    <x-slot:filters>
        <select name="status" onchange="this.form.submit()" class="rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm px-3.5 py-2.5 brand-ring shadow-sm">
            <option value="">All statuses</option>
            <option value="1" @selected(request('status')==='1')>Active</option>
            <option value="0" @selected(request('status')==='0')>Disabled</option>
        </select>
    </x-slot:filters>
</x-search>

<x-table :headings="['Customer', 'Phone', 'Status', 'Joined', '']">
    @forelse($customers as $customer)
    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/60">
        <td class="px-4 py-3">
            <div class="flex items-center gap-3">
                <span class="h-9 w-9 rounded-full brand-gradient text-white text-xs font-semibold flex items-center justify-center">{{ $customer->initials() }}</span>
                <div><p class="font-medium text-slate-800 dark:text-slate-100">{{ $customer->name }}</p><p class="text-xs text-slate-400">{{ $customer->email }}</p></div>
            </div>
        </td>
        <td class="px-4 py-3 text-slate-500">{{ $customer->phone ?? '—' }}</td>
        <td class="px-4 py-3"><x-badge :color="$customer->status ? 'green' : 'red'">{{ $customer->status ? 'Active' : 'Disabled' }}</x-badge></td>
        <td class="px-4 py-3 text-slate-500">{{ $customer->created_at->format('M j, Y') }}</td>
        <td class="px-4 py-3">
            <div class="flex items-center justify-end gap-1">
                @can('customers.edit')<x-icon-btn icon="edit" :href="route('admin.customers.edit', $customer)" label="Edit" />@endcan
                @can('customers.delete')
                <form method="POST" action="{{ route('admin.customers.destroy', $customer) }}" onsubmit="return confirm('Delete this user?')">@csrf @method('DELETE')
                    <x-icon-btn icon="trash" type="submit" variant="danger" label="Delete" /></form>
                @endcan
            </div>
        </td>
    </tr>
    @empty
    <tr><td colspan="5" class="px-4 py-12 text-center text-slate-400">No customers found.</td></tr>
    @endforelse
</x-table>
{{ $customers->links() }}
@endsection
