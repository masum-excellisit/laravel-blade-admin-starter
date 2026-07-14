@props(['title', 'subtitle' => null])
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <div>
        <h1 class="text-2xl font-bold text-slate-800 dark:text-white">{{ $title }}</h1>
        @if($subtitle)<p class="text-sm text-slate-500 dark:text-slate-400 mt-1">{{ $subtitle }}</p>@endif
    </div>
    @isset($actions)<div class="flex items-center gap-2">{{ $actions }}</div>@endisset
</div>
