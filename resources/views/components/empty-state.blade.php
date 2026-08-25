@props(['message' => 'Nothing here yet.', 'icon' => 'inbox'])
<div {{ $attributes->merge(['class' => 'ez-empty']) }}>
    <span class="ez-empty__icon"><x-icon :name="$icon" class="w-5 h-5" /></span>
    <p>{{ $message }}</p>
    @isset($action)<div class="mt-3">{{ $action }}</div>@endisset
</div>
