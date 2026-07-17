@csrf
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6" x-data="{ type: @js(old('type', $block->type ?? 'html')) }" x-init="$watch('type', () => $nextTick(() => { if (type === 'richtext' && window.initEditors) window.initEditors($root); }))">
    <div class="lg:col-span-2 space-y-5">
        <x-form.input name="name" label="Name" :value="$block->name" required />
        <x-form.input name="key" label="Key" :value="$block->key" hint="Slug-like unique key used by block('key')" />

        <div>
            <x-form.label for="content">Content</x-form.label>
            <textarea
                name="content"
                id="content"
                rows="14"
                x-bind:data-jodit="type === 'richtext' ? true : null"
                data-upload-url="{{ route('admin.media.jodit') }}"
                class="w-full rounded-xl border border-slate-300 dark:border-slate-600 dark:bg-slate-900/50 dark:text-white shadow-sm brand-ring focus:border-primary text-sm px-3.5 py-2.5"
            >{{ old('content', $block->content) }}</textarea>
            @error('content')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
        </div>
    </div>

    <div class="space-y-5">
        <x-card title="Settings">
            <div class="space-y-4">
                <x-form.select name="type" label="Type" :options="[
                    'html' => 'HTML',
                    'richtext' => 'Rich text',
                    'json' => 'JSON',
                ]" :selected="$block->type ?? 'html'" required x-model="type" />
                <label class="flex items-center gap-2 text-sm text-slate-700 dark:text-slate-300">
                    <input type="checkbox" name="is_active" value="1" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500" @checked(old('is_active', $block->is_active ?? true))>
                    Active
                </label>
                <x-btn type="submit" class="w-full">{{ $block->exists ? 'Update block' : 'Create block' }}</x-btn>
            </div>
        </x-card>
    </div>
</div>
