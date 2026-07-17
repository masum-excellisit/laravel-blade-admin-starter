@props([
    'action' => null,
    'options' => [
        'delete' => 'Delete selected',
    ],
])

<div x-show="selected.length > 0"
     x-cloak
     x-transition
     class="mb-4 flex flex-wrap items-center gap-3 rounded-2xl border border-indigo-200/70 dark:border-indigo-800/50 bg-indigo-50/80 dark:bg-indigo-950/40 px-4 py-3">
    <p class="text-sm font-medium text-indigo-800 dark:text-indigo-200">
        <span x-text="selected.length"></span> selected
    </p>
    <form method="POST" action="{{ $action }}" class="flex flex-wrap items-center gap-2"
          x-on:submit="if (!action) { $event.preventDefault(); return false; } if (action === 'delete' && !confirm('Delete selected items?')) { $event.preventDefault(); return false; }"
          x-data="{ action: '' }">
        @csrf
        <template x-for="id in selected" :key="id">
            <input type="hidden" name="ids[]" :value="id">
        </template>
        <select name="action" x-model="action" required
                class="rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 text-sm px-3 py-2">
            <option value="">Choose action…</option>
            @foreach($options as $value => $label)
                <option value="{{ $value }}">{{ $label }}</option>
            @endforeach
        </select>
        <x-btn type="submit" size="sm" variant="secondary">Apply</x-btn>
    </form>
</div>
