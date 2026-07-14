@csrf
<x-form.input name="name" label="Role name" :value="$role->name" required class="max-w-sm" />
<div class="mt-6">
    <x-form.label>Permissions</x-form.label>
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4 mt-2">
        @foreach($permissions as $module => $perms)
        <div class="rounded-xl border border-slate-200 dark:border-slate-700 p-4" x-data="{ all:false }">
            <div class="flex items-center justify-between mb-2">
                <p class="font-semibold text-sm capitalize">{{ $module }}</p>
                <button type="button" x-on:click="all=!all; $refs.g.querySelectorAll('input').forEach(i=>i.checked=all)" class="text-xs brand-gradient-text font-medium">Toggle</button>
            </div>
            <div x-ref="g" class="space-y-1.5">
                @foreach($perms as $perm)
                <label class="flex items-center gap-2 text-sm">
                    <input type="checkbox" name="permissions[]" value="{{ $perm->name }}" class="rounded text-primary brand-ring" @checked(in_array($perm->name, old('permissions', $assigned ?? [])))>
                    <span class="text-slate-600 dark:text-slate-300">{{ explode('.', $perm->name)[1] ?? $perm->name }}</span>
                </label>
                @endforeach
            </div>
        </div>
        @endforeach
    </div>
</div>
<div class="mt-6 flex gap-2">
    <x-btn type="submit">{{ $role->exists ? 'Update role' : 'Create role' }}</x-btn>
    <x-btn variant="outline" :href="route('admin.roles.index')">Cancel</x-btn>
</div>
