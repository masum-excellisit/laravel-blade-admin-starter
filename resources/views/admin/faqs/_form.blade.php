@csrf
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-5">
        <x-form.input name="question" label="Question" :value="$faq->question" required />
        <x-form.textarea name="answer" label="Answer" :value="$faq->answer" rows="6" required />
        <x-form.input name="category" label="Category" :value="$faq->category" />
    </div>
    <div class="space-y-5">
        <x-card title="Publish">
            <div class="space-y-4">
                <x-form.select name="status" label="Status" :options="['draft'=>'Draft','published'=>'Published']" :selected="$faq->status" />
                <x-form.input name="sort_order" type="number" label="Sort order" :value="$faq->sort_order ?? 0" min="0" />
                <x-btn type="submit" class="w-full">{{ $faq->exists ? 'Update FAQ' : 'Create FAQ' }}</x-btn>
            </div>
        </x-card>
    </div>
</div>
