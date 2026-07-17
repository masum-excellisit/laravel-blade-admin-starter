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
            <x-form.image name="featured_image" rounded="rounded-xl"
                :current="$post->featured_image ? \Illuminate\Support\Facades\Storage::disk('public')->url($post->featured_image) : ''" />
        </x-card>
        @if($post->exists)
            @include('admin.revisions._list', [
                'revisions' => $revisions ?? collect(),
                'restoreRouteName' => 'admin.posts.revisions.restore',
                'model' => $post,
                'permission' => 'posts.edit',
            ])
        @endif
        <x-seo-fields :model="$post" />
    </div>
</div>
