@csrf
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-5">
        <x-form.input name="title" label="Title" :value="$service->title" required />
        <x-form.input name="slug" label="Slug" :value="$service->slug" hint="Leave blank to auto-generate from title" />
        <x-form.textarea name="excerpt" label="Excerpt" :value="$service->excerpt" rows="2" />
        <div>
            <x-form.label for="body">Body</x-form.label>
            <textarea name="body" id="body" data-jodit data-upload-url="{{ route('admin.media.jodit') }}">{{ old('body', $service->body) }}</textarea>
        </div>
        <x-form.input name="icon" label="Icon" :value="$service->icon" hint="Icon class or identifier" />
    </div>
    <div class="space-y-5">
        <x-card title="Publish">
            <div class="space-y-4">
                <x-form.select name="status" label="Status" :options="['draft'=>'Draft','published'=>'Published']" :selected="$service->status" />
                <x-form.input name="sort_order" type="number" label="Sort order" :value="$service->sort_order ?? 0" min="0" />
                <x-btn type="submit" class="w-full">{{ $service->exists ? 'Update service' : 'Create service' }}</x-btn>
            </div>
        </x-card>
        <x-card title="Image">
            <x-form.image name="image" rounded="rounded-xl"
                :current="$service->image ? \Illuminate\Support\Facades\Storage::disk('public')->url($service->image) : ''" />
        </x-card>
    </div>
</div>
