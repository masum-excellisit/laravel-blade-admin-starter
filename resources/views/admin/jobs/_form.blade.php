@csrf
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-5">
        <x-form.input name="title" label="Title" :value="$jobListing->title" required />
        <x-form.input name="slug" label="Slug" :value="$jobListing->slug" hint="Leave blank to auto-generate from title" />
        <x-form.input name="location" label="Location" :value="$jobListing->location" />
        <div>
            <x-form.label for="description">Description</x-form.label>
            <textarea name="description" id="description" data-jodit data-upload-url="{{ route('admin.media.jodit') }}">{{ old('description', $jobListing->description) }}</textarea>
        </div>
        <div>
            <x-form.label for="requirements">Requirements</x-form.label>
            <textarea name="requirements" id="requirements" data-jodit data-upload-url="{{ route('admin.media.jodit') }}">{{ old('requirements', $jobListing->requirements) }}</textarea>
        </div>
    </div>
    <div class="space-y-5">
        <x-card title="Publish">
            <div class="space-y-4">
                <x-form.select name="employment_type" label="Employment type" :options="[
                    'full-time' => 'Full-time',
                    'part-time' => 'Part-time',
                    'contract' => 'Contract',
                    'remote' => 'Remote',
                ]" :selected="$jobListing->employment_type" />
                <x-form.select name="status" label="Status" :options="['draft'=>'Draft','published'=>'Published']" :selected="$jobListing->status" />
                <x-form.input name="published_at" type="datetime-local" label="Publish at" :value="old('published_at', $jobListing->published_at?->format('Y-m-d\TH:i'))" />
                <x-btn type="submit" class="w-full">{{ $jobListing->exists ? 'Update job' : 'Create job' }}</x-btn>
            </div>
        </x-card>
    </div>
</div>
