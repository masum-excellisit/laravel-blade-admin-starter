@props(['name', 'title' => null])
<div x-data="{ open: false }"
     x-on:open-modal.window="if ($event.detail === '{{ $name }}') open = true"
     x-on:close-modal.window="if ($event.detail === '{{ $name }}') open = false"
     x-on:keydown.escape.window="open = false"
     style="display:none" x-show="open" class="fixed inset-0 z-50 flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" x-on:click="open = false"></div>
    <div x-show="open" x-transition class="relative bg-white dark:bg-slate-800 rounded-2xl shadow-2xl w-full max-w-lg">
        @if($title)<div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 font-semibold text-slate-800 dark:text-white">{{ $title }}</div>@endif
        <div class="p-6">{{ $slot }}</div>
    </div>
</div>
