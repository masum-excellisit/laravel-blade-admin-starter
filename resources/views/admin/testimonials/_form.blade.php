@csrf
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-5">
        <x-form.input name="author_name" label="Author name" :value="$testimonial->author_name" required />
        <x-form.input name="author_title" label="Author title" :value="$testimonial->author_title" />
        <x-form.textarea name="quote" label="Quote" :value="$testimonial->quote" rows="4" required />
    </div>
    <div class="space-y-5">
        <x-card title="Publish">
            <div class="space-y-4">
                <x-form.select name="status" label="Status" :options="['draft'=>'Draft','published'=>'Published']" :selected="$testimonial->status" />
                <x-form.input name="rating" type="number" label="Rating (1–5)" :value="$testimonial->rating ?? 5" min="1" max="5" required />
                <x-form.input name="sort_order" type="number" label="Sort order" :value="$testimonial->sort_order ?? 0" min="0" />
                <x-btn type="submit" class="w-full">{{ $testimonial->exists ? 'Update testimonial' : 'Create testimonial' }}</x-btn>
            </div>
        </x-card>
        <x-card title="Avatar">
            <x-form.image name="avatar" rounded="rounded-full"
                :current="$testimonial->avatar ? \Illuminate\Support\Facades\Storage::disk('public')->url($testimonial->avatar) : ''" />
        </x-card>
    </div>
</div>
