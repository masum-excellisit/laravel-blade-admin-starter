@csrf
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-5">
        <x-form.input name="title" label="Title" :value="$page->title" required />
        <x-form.input name="slug" label="Slug" :value="$page->slug" hint="Leave blank to auto-generate from title" />
        <div>
            <x-form.label for="body">Content</x-form.label>
            <textarea name="body" id="body" data-jodit data-upload-url="{{ route('admin.media.jodit') }}">{{ old('body', $page->body) }}</textarea>
        </div>
    </div>
    <div class="space-y-5">
        <x-card title="Publish" >
            <div class="space-y-4">
                <x-form.select name="status" label="Status" :options="['draft'=>'Draft','published'=>'Published']" :selected="$page->status" />
                <x-form.select name="template" label="Template" :options="$templates" :selected="$page->template" />
                <x-btn type="submit" class="w-full">{{ $page->exists ? 'Update page' : 'Create page' }}</x-btn>
            </div>
        </x-card>
        <x-seo-fields :model="$page" />
    </div>
</div>
