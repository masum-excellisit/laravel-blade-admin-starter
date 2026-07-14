@csrf
<div class="grid grid-cols-1 md:grid-cols-2 gap-5">
    <x-form.input name="name" label="Name" :value="$user->name" required />
    <x-form.input name="email" type="email" label="Email" :value="$user->email" required />
    <x-form.input name="phone" label="Phone" :value="$user->phone" />
    <x-form.input name="avatar" type="file" label="Avatar" accept="image/*" />
    <x-form.input name="password" type="password" label="Password" :required="!$user->exists" :hint="$user->exists ? 'Leave blank to keep current' : null" />
    <x-form.input name="password_confirmation" type="password" label="Confirm password" />
</div>
<div class="mt-5">
    <x-form.label>Roles</x-form.label>
    <div class="flex flex-wrap gap-2">
        @foreach($roles as $role)
        <label class="flex items-center gap-2 px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 cursor-pointer has-[:checked]:border-primary has-[:checked]:bg-primary-soft">
            <input type="checkbox" name="roles[]" value="{{ $role }}" class="rounded text-primary brand-ring" @checked(in_array($role, old('roles', $user->roles->pluck('name')->toArray())))>
            <span class="text-sm">{{ $role }}</span>
        </label>
        @endforeach
    </div>
</div>
<label class="flex items-center gap-2 mt-5 text-sm">
    <input type="checkbox" name="status" value="1" class="rounded text-primary brand-ring" @checked(old('status', $user->status ?? true))>
    Account active
</label>
<div class="mt-6 flex gap-2">
    <x-btn type="submit">{{ $user->exists ? 'Update user' : 'Create user' }}</x-btn>
    <x-btn variant="outline" :href="route('admin.users.index')">Cancel</x-btn>
</div>
