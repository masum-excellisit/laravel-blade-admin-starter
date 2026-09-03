@props(['model', 'fallbackTitle' => null])

@php
    $fallback = old('title', $fallbackTitle ?? $model->title ?? '');
@endphp

<x-card title="SEO">
    <div
        class="space-y-4"
        x-data="{
            title: @js(old('meta_title', $model->meta_title ?? '')),
            fallback: @js($fallback),
            description: @js(old('meta_description', $model->meta_description ?? '')),
            get effectiveTitle() { return (this.title || '').trim() || (this.fallback || ''); },
            get titleLen() { return this.effectiveTitle.length; }
        }"
    >
        <x-form.input name="meta_title" label="SEO title" :value="$model->meta_title ?? ''" x-model="title" hint="Leave blank to use the post title." />
        <x-form.textarea name="meta_description" label="Meta description" :value="$model->meta_description ?? ''" rows="3" x-model="description" />
        <x-form.input name="og_image" label="OG image URL" :value="$model->og_image ?? ''" />
        <x-form.input name="canonical_url" label="Canonical URL" :value="$model->canonical_url ?? ''" />

        <div class="rounded-xl bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 p-4">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">SEO checklist</p>
            <ul class="mt-3 space-y-2 text-sm">
                <li class="flex items-center gap-2" :class="titleLen >= 30 && titleLen <= 60 ? 'text-emerald-600' : 'text-slate-500'">
                    <span x-text="titleLen >= 30 && titleLen <= 60 ? 'OK' : '-'"></span>
                    <span>
                        SEO title length:
                        <span x-text="titleLen"></span>/60 characters
                        <span class="text-xs text-slate-400" x-show="!(title || '').trim() && (fallback || '').trim()" x-cloak>(using post title)</span>
                    </span>
                </li>
                <li class="flex items-center gap-2" :class="description.length >= 120 && description.length <= 160 ? 'text-emerald-600' : 'text-slate-500'">
                    <span x-text="description.length >= 120 && description.length <= 160 ? 'OK' : '-'"></span>
                    <span>Description length: <span x-text="description.length"></span>/160 characters</span>
                </li>
            </ul>
        </div>
    </div>
</x-card>
