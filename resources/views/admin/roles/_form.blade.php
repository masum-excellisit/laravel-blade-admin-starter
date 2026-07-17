@csrf
<x-form.input name="name" label="Role name" :value="$role->name" required class="max-w-sm" />
<div class="mt-6">
    <x-form.label>Permissions</x-form.label>
    <p class="mt-1 text-sm text-slate-500">Choose which module actions this role can perform.</p>
    <div class="mt-4">
        <x-permission-modules
            :modules="$permissions"
            selectable
            searchable
            :assigned="old('permissions', $assigned ?? [])"
        />
    </div>
</div>
<div class="mt-6 flex gap-2">
    <x-btn type="submit">{{ $role->exists ? 'Update role' : 'Create role' }}</x-btn>
    <x-btn variant="outline" :href="route('admin.roles.index')">Cancel</x-btn>
</div>
