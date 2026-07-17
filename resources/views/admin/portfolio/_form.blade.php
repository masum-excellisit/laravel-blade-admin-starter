@csrf
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-5">
        <x-form.input name="title" label="Title" :value="$portfolioItem->title" required />
        <x-form.input name="slug" label="Slug" :value="$portfolioItem->slug" hint="Leave blank to auto-generate from title" />
        <x-form.input name="client" label="Client" :value="$portfolioItem->client" />
        <x-form.textarea name="excerpt" label="Excerpt" :value="$portfolioItem->excerpt" rows="3" />
        <x-form.textarea name="body" label="Body" :value="$portfolioItem->body" rows="8" />
        <x-form.input name="project_url" type="url" label="Project URL" :value="$portfolioItem->project_url" />
    </div>
    <div class="space-y-5">
        <x-card title="Publish">
            <div class="space-y-4">
                <x-form.select name="status" label="Status" :options="['draft'=>'Draft','published'=>'Published']" :selected="$portfolioItem->status" />
                <x-form.input name="published_at" type="datetime-local" label="Publish at" :value="old('published_at', $portfolioItem->published_at?->format('Y-m-d\TH:i'))" />
                <x-form.input name="sort_order" type="number" label="Sort order" :value="$portfolioItem->sort_order ?? 0" min="0" />
                <x-btn type="submit" class="w-full">{{ $portfolioItem->exists ? 'Update portfolio item' : 'Create portfolio item' }}</x-btn>
            </div>
        </x-card>
        <x-card title="Image">
            <x-form.image name="image" rounded="rounded-xl"
                :current="$portfolioItem->image ? \Illuminate\Support\Facades\Storage::disk('public')->url($portfolioItem->image) : ''" />
        </x-card>
    </div>
</div>
