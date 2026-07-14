@props(['name', 'label' => null, 'current' => null, 'rounded' => 'rounded-2xl', 'hint' => null])
<div x-data="{ preview: @js($current ?: '') }">
    @if($label)<x-form.label :for="$name">{{ $label }}</x-form.label>@endif
    <div class="flex items-center gap-4">
        <div class="h-20 w-20 shrink-0 {{ $rounded }} border-2 border-dashed border-slate-200 dark:border-slate-600 overflow-hidden bg-slate-50 dark:bg-slate-800 flex items-center justify-center">
            <template x-if="preview"><img :src="preview" class="h-full w-full object-cover"></template>
            <template x-if="!preview"><x-icon name="upload" class="w-6 h-6 text-slate-300" /></template>
        </div>
        <div class="flex-1">
            <input type="file" name="{{ $name }}" id="{{ $name }}" accept="image/*"
                   x-on:change="const f=$event.target.files[0]; if(f) preview=URL.createObjectURL(f)"
                   {{ $attributes->merge(['class' => 'block w-full text-sm text-slate-500 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-primary-soft file:text-primary hover:file:brightness-95 cursor-pointer']) }}>
            @if($hint)<p class="mt-1.5 text-xs text-slate-400">{{ $hint }}</p>@endif
            @error($name)<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
        </div>
    </div>
</div>
