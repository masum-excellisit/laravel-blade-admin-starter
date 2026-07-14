@props(['name', 'label' => null, 'value' => null, 'rows' => 4, 'required' => false])
<div>
    @if($label)<x-form.label :for="$name" :required="$required">{{ $label }}</x-form.label>@endif
    <textarea name="{{ $name }}" id="{{ $name }}" rows="{{ $rows }}"
        {{ $attributes->merge(['class' => 'w-full rounded-xl border border-slate-300 dark:border-slate-600 dark:bg-slate-900/50 dark:text-white shadow-sm brand-ring focus:border-primary text-sm px-3.5 py-2.5']) }}>{{ old($name, $value) }}</textarea>
    @error($name)<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
</div>
