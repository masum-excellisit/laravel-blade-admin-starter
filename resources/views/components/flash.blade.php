@if(session('success') || session('error') || session('status'))
<div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)" x-transition
     class="fixed top-5 right-5 z-[60] max-w-sm">
    @php
        $msg = session('success') ?? session('status') ?? session('error');
        $ok = ! session('error');
    @endphp
    <div class="flex items-start gap-3 rounded-xl px-4 py-3 shadow-lg text-white {{ $ok ? 'bg-emerald-600' : 'bg-red-600' }}">
        <span class="text-sm font-medium">{{ $msg }}</span>
        <button type="button" x-on:click="show = false" class="ml-auto opacity-80 hover:opacity-100">&times;</button>
    </div>
</div>
@endif
