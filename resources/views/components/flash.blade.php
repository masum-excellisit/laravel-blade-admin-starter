@if(session('success') || session('error') || session('status'))
<div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)" x-transition
     class="fixed top-5 right-5 z-[60] max-w-sm">
    @php
        $msg = session('success') ?? session('status') ?? session('error');
        $ok = ! session('error');
    @endphp
    <div class="flex items-start gap-3 rounded-xl px-4 py-3 shadow-lg text-white {{ $ok ? 'bg-emerald-600' : 'bg-red-600' }}">
        <span class="text-sm font-medium">{{ $msg }}</span>
        <button x-on:click="show = false" class="ml-auto opacity-80 hover:opacity-100">&times;</button>
    </div>
</div>
@endif
@if($errors->any())
<div x-data="{ show: true }" x-show="show" x-transition class="fixed top-5 right-5 z-[55] max-w-sm mt-20">
    <div class="rounded-xl px-4 py-3 shadow-lg bg-red-600 text-white text-sm">
        <div class="flex justify-between"><strong>Please fix the errors below.</strong><button x-on:click="show=false" class="ml-3">&times;</button></div>
    </div>
</div>
@endif
