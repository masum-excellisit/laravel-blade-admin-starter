@props(['id'])
<td class="px-4 py-3 w-10">
    <input type="checkbox" data-bulk-id value="{{ $id }}"
           class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
           x-on:change="toggle('{{ $id }}', $event.target.checked)">
</td>
