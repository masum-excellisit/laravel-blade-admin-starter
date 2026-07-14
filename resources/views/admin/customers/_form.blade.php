@csrf
<div class="mb-5">
    <x-form.image name="avatar" label="Avatar" rounded="rounded-full"
        :current="$customer->avatar ? \Illuminate\Support\Facades\Storage::disk('public')->url($customer->avatar) : ''" />
</div>
<div class="grid grid-cols-1 md:grid-cols-2 gap-5">
    <x-form.input name="name" label="Name" :value="$customer->name" required />
    <x-form.input name="email" type="email" label="Email" :value="$customer->email" required />
    <x-form.input name="phone" label="Phone" :value="$customer->phone" />
    <div></div>
    <x-form.input name="password" type="password" label="Password" :required="!$customer->exists" :hint="$customer->exists ? 'Leave blank to keep current' : null" />
    <x-form.input name="password_confirmation" type="password" label="Confirm password" />
</div>
<label class="flex items-center gap-2 mt-5 text-sm">
    <input type="checkbox" name="status" value="1" class="rounded text-primary brand-ring" @checked(old('status', $customer->status ?? true))>
    Account active
</label>
<div class="mt-6 flex gap-2">
    <x-btn type="submit">{{ $customer->exists ? 'Update user' : 'Create user' }}</x-btn>
    <x-btn variant="outline" :href="route('admin.customers.index')">Cancel</x-btn>
</div>
