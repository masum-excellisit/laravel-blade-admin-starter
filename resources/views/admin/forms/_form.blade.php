@csrf
@php
    $fieldRows = old('fields') ?? ($form->exists
        ? $form->fields->map(fn ($field) => [
            'label' => $field->label,
            'name' => $field->name,
            'type' => $field->type,
            'options' => implode(', ', $field->options ?? []),
            'required' => $field->required,
            'sort_order' => $field->sort_order,
        ])->values()->all()
        : []);

    if ($fieldRows === []) {
        $fieldRows[] = ['label' => '', 'name' => '', 'type' => 'text', 'options' => '', 'required' => false, 'sort_order' => 0];
    }
@endphp
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-5">
        <x-form.input name="name" label="Name" :value="$form->name" required />
        <x-form.input name="slug" label="Slug" :value="$form->slug" hint="Leave blank to auto-generate from name" />
        <x-form.input name="success_message" label="Success message" :value="$form->success_message" required />
        <x-form.input name="notify_email" type="email" label="Notify email" :value="$form->notify_email" hint="Optional email address for new submission summaries" />

        <x-card title="Fields">
            <div
                class="space-y-4"
                x-data="{
                    fields: @js($fieldRows),
                    add() {
                        this.fields.push({ label: '', name: '', type: 'text', options: '', required: false, sort_order: this.fields.length * 10 });
                    },
                    remove(index) {
                        this.fields.splice(index, 1);
                        if (this.fields.length === 0) this.add();
                    },
                    slugify(value) {
                        return (value || '').toLowerCase().trim().replace(/[^a-z0-9]+/g, '_').replace(/^_+|_+$/g, '');
                    },
                    fillName(field) {
                        if (!field.name) field.name = this.slugify(field.label);
                    }
                }"
            >
                <template x-for="(field, index) in fields" :key="index">
                    <div class="rounded-2xl border border-slate-200 dark:border-slate-700 p-4 space-y-4 bg-slate-50/60 dark:bg-slate-900/30">
                        <div class="flex items-center justify-between gap-3">
                            <h4 class="font-semibold text-sm text-slate-700 dark:text-slate-200">Field <span x-text="index + 1"></span></h4>
                            <button type="button" x-on:click="remove(index)" class="text-sm text-red-600 hover:underline">Remove</button>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <x-form.label>Label</x-form.label>
                                <input type="text" x-model="field.label" x-on:blur="fillName(field)" :name="`fields[${index}][label]`" class="w-full rounded-xl border border-slate-300 dark:border-slate-600 dark:bg-slate-900/50 dark:text-white shadow-sm brand-ring focus:border-primary text-sm px-3.5 py-2.5">
                            </div>
                            <div>
                                <x-form.label>Name</x-form.label>
                                <input type="text" x-model="field.name" :name="`fields[${index}][name]`" class="w-full rounded-xl border border-slate-300 dark:border-slate-600 dark:bg-slate-900/50 dark:text-white shadow-sm brand-ring focus:border-primary text-sm px-3.5 py-2.5">
                            </div>
                            <div>
                                <x-form.label>Type</x-form.label>
                                <select x-model="field.type" :name="`fields[${index}][type]`" class="w-full rounded-xl border border-slate-300 dark:border-slate-600 dark:bg-slate-900/50 dark:text-white shadow-sm brand-ring focus:border-primary text-sm px-3.5 py-2.5">
                                    <option value="text">Text</option>
                                    <option value="email">Email</option>
                                    <option value="textarea">Textarea</option>
                                    <option value="select">Select</option>
                                    <option value="checkbox">Checkbox</option>
                                    <option value="number">Number</option>
                                    <option value="tel">Telephone</option>
                                    <option value="url">URL</option>
                                </select>
                            </div>
                            <div>
                                <x-form.label>Sort order</x-form.label>
                                <input type="number" min="0" x-model="field.sort_order" :name="`fields[${index}][sort_order]`" class="w-full rounded-xl border border-slate-300 dark:border-slate-600 dark:bg-slate-900/50 dark:text-white shadow-sm brand-ring focus:border-primary text-sm px-3.5 py-2.5">
                            </div>
                        </div>
                        <div x-show="field.type === 'select'" x-cloak>
                            <x-form.label>Options</x-form.label>
                            <input type="text" x-model="field.options" :name="`fields[${index}][options]`" placeholder="Small, Medium, Large" class="w-full rounded-xl border border-slate-300 dark:border-slate-600 dark:bg-slate-900/50 dark:text-white shadow-sm brand-ring focus:border-primary text-sm px-3.5 py-2.5">
                            <p class="mt-1 text-xs text-slate-400">Comma-separated choices for select fields.</p>
                        </div>
                        <label class="flex items-center gap-2 text-sm text-slate-700 dark:text-slate-300">
                            <input type="checkbox" value="1" x-model="field.required" :name="`fields[${index}][required]`" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                            Required
                        </label>
                    </div>
                </template>

                <button type="button" x-on:click="add()" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 text-sm font-semibold rounded-xl border border-slate-300 text-slate-700 hover:bg-slate-50 dark:border-slate-600 dark:text-slate-200 dark:hover:bg-slate-800">
                    Add field
                </button>
            </div>
            @error('fields')<p class="mt-3 text-xs text-red-500">{{ $message }}</p>@enderror
        </x-card>
    </div>

    <div class="space-y-5">
        <x-card title="Publish">
            <div class="space-y-4">
                <label class="flex items-center gap-2 text-sm text-slate-700 dark:text-slate-300">
                    <input type="checkbox" name="is_active" value="1" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500" @checked(old('is_active', $form->is_active ?? true))>
                    Active
                </label>
                <x-btn type="submit" class="w-full">{{ $form->exists ? 'Update form' : 'Create form' }}</x-btn>
            </div>
        </x-card>
    </div>
</div>
