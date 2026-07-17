@props(['model'])

<x-card title="SEO">
    <div
        class="space-y-4"
        x-data="{
            title: @js(old('meta_title', $model->meta_title ?? '')),
            description: @js(old('meta_description', $model->meta_description ?? ''))
        }"
    >
        <x-form.input name="meta_title" label="Meta title" :value="$model->meta_title ?? ''" x-model="title" />
        <x-form.textarea name="meta_description" label="Meta description" :value="$model->meta_description ?? ''" rows="3" x-model="description" />
        <x-form.input name="og_image" label="OG image URL" :value="$model->og_image ?? ''" />
        <x-form.input name="canonical_url" label="Canonical URL" :value="$model->canonical_url ?? ''" />

        <div class="rounded-xl bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 p-4">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">SEO checklist</p>
            <ul class="mt-3 space-y-2 text-sm">
                <li class="flex items-center gap-2" :class="title.length >= 30 && title.length <= 60 ? 'text-emerald-600' : 'text-slate-500'">
                    <span x-text="title.length >= 30 && title.length <= 60 ? 'OK' : '-'"></span>
                    <span>Title length: <span x-text="title.length"></span>/60 characters</span>
                </li>
                <li class="flex items-center gap-2" :class="description.length >= 120 && description.length <= 160 ? 'text-emerald-600' : 'text-slate-500'">
                    <span x-text="description.length >= 120 && description.length <= 160 ? 'OK' : '-'"></span>
                    <span>Description length: <span x-text="description.length"></span>/160 characters</span>
                </li>
            </ul>
        </div>
    </div>
</x-card>
