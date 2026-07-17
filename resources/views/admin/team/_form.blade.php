@csrf
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-5">
        <x-form.input name="name" label="Name" :value="$teamMember->name" required />
        <x-form.input name="role_title" label="Role title" :value="$teamMember->role_title" />
        <x-form.textarea name="bio" label="Bio" :value="$teamMember->bio" rows="5" />
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <x-form.input name="email" type="email" label="Email" :value="$teamMember->email" />
            <x-form.input name="linkedin" type="url" label="LinkedIn URL" :value="$teamMember->linkedin" />
        </div>
    </div>
    <div class="space-y-5">
        <x-card title="Publish">
            <div class="space-y-4">
                <x-form.select name="status" label="Status" :options="['draft'=>'Draft','published'=>'Published']" :selected="$teamMember->status" />
                <x-form.input name="sort_order" type="number" label="Sort order" :value="$teamMember->sort_order ?? 0" min="0" />
                <x-btn type="submit" class="w-full">{{ $teamMember->exists ? 'Update team member' : 'Create team member' }}</x-btn>
            </div>
        </x-card>
        <x-card title="Photo">
            <x-form.image name="photo" rounded="rounded-full"
                :current="$teamMember->photo ? \Illuminate\Support\Facades\Storage::disk('public')->url($teamMember->photo) : ''" />
        </x-card>
    </div>
</div>
