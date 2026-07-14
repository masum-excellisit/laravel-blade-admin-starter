@csrf
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-5">
        <x-form.input name="title" label="Title" :value="$post->title" required />
        <x-form.input name="slug" label="Slug" :value="$post->slug" hint="Leave blank to auto-generate" />
        <x-form.textarea name="excerpt" label="Excerpt" :value="$post->excerpt" rows="2" />
        <div>
            <x-form.label for="body">Content</x-form.label>
            <textarea name="body" id="body" data-jodit data-upload-url="{{ route('admin.media.jodit') }}">{{ old('body', $post->body) }}</textarea>
        </div>
    </div>
    <div class="space-y-5">
        <x-card title="Publish">
            <div class="space-y-4">
                <x-form.select name="status" label="Status" :options="['draft'=>'Draft','published'=>'Published']" :selected="$post->status" />
                <x-form.input name="published_at" type="datetime-local" label="Publish at" :value="old('published_at', $post->published_at?->format('Y-m-d\TH:i'))" />
                <x-form.select name="category_id" label="Category" :options="$categories" :selected="$post->category_id" placeholder="— none —" />
                <x-btn type="submit" class="w-full">{{ $post->exists ? 'Update post' : 'Create post' }}</x-btn>
            </div>
        </x-card>
        <x-card title="Featured image">
            @if($post->featured_image)<img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($post->featured_image) }}" class="rounded-xl mb-3 w-full object-cover">@endif
            <x-form.input name="featured_image" type="file" accept="image/*" />
        </x-card>
    </div>
</div>
