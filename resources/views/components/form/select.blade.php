@props(['name', 'label' => null, 'options' => [], 'selected' => null, 'required' => false, 'placeholder' => null])
<div>
    @if($label)<x-form.label :for="$name" :required="$required">{{ $label }}</x-form.label>@endif
    <select name="{{ $name }}" id="{{ $name }}"
        {{ $attributes->merge(['class' => 'w-full rounded-xl border border-slate-300 dark:border-slate-600 dark:bg-slate-900/50 dark:text-white shadow-sm brand-ring focus:border-primary text-sm px-3.5 py-2.5']) }}>
        @if($placeholder)<option value="">{{ $placeholder }}</option>@endif
        @foreach($options as $val => $text)
            <option value="{{ $val }}" @selected((string) old($name, $selected) === (string) $val)>{{ $text }}</option>
        @endforeach
    </select>
    @error($name)<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
</div>
