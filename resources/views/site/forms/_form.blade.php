<form method="POST" action="{{ route('forms.submit', $form->slug) }}" class="space-y-5">
    @csrf
    @foreach($form->fields as $field)
        <div>
            @if($field->type === 'textarea')
                <x-form.textarea :name="$field->name" :label="$field->label" :required="$field->required" rows="5" />
            @elseif($field->type === 'select')
                <x-form.label :for="$field->name" :required="$field->required">{{ $field->label }}</x-form.label>
                <select name="{{ $field->name }}" id="{{ $field->name }}" class="w-full rounded-xl border border-slate-300 dark:border-slate-600 dark:bg-slate-900/50 dark:text-white shadow-sm brand-ring focus:border-primary text-sm px-3.5 py-2.5">
                    <option value="">Choose...</option>
                    @foreach($field->options ?? [] as $option)
                        <option value="{{ $option }}" @selected(old($field->name) === $option)>{{ $option }}</option>
                    @endforeach
                </select>
                @error($field->name)<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            @elseif($field->type === 'checkbox')
                <input type="hidden" name="{{ $field->name }}" value="0">
                <label class="flex items-center gap-2 text-sm text-slate-700 dark:text-slate-300">
                    <input type="checkbox" name="{{ $field->name }}" value="1" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500" @checked(old($field->name))>
                    {{ $field->label }}@if($field->required)<span class="text-red-500">*</span>@endif
                </label>
                @error($field->name)<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            @else
                <x-form.input
                    :name="$field->name"
                    :label="$field->label"
                    :type="$field->type"
                    :required="$field->required"
                />
            @endif
        </div>
    @endforeach
    <x-btn type="submit" class="w-full">Submit</x-btn>
</form>
