@php
    $cookieEnabled = in_array(strtolower((string) ($settings['cookie_enabled'] ?? '0')), ['1', 'true', 'yes', 'on'], true);
    $cookieMessage = $settings['cookie_message'] ?? 'We use cookies to improve your browsing experience.';
    $cookiePolicyUrl = trim((string) ($settings['cookie_policy_url'] ?? ''));
@endphp

@if($cookieEnabled)
<div
    x-data="{
        visible: localStorage.getItem('cookie_consent') !== 'accepted',
        accept() {
            localStorage.setItem('cookie_consent', 'accepted');
            this.visible = false;
        }
    }"
    x-show="visible"
    x-cloak
    x-transition
    class="fixed inset-x-0 bottom-0 z-50 px-4 pb-4 sm:px-6"
>
    <div class="max-w-5xl mx-auto rounded-2xl bg-slate-950 text-white shadow-2xl border border-white/10 p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center gap-4">
        <p class="text-sm text-white/80 flex-1">
            {{ $cookieMessage }}
            @if($cookiePolicyUrl)
                <a href="{{ $cookiePolicyUrl }}" class="font-semibold text-white underline underline-offset-4">Cookie policy</a>
            @endif
        </p>
        <x-btn type="button" variant="secondary" size="sm" x-on:click="accept()">Accept</x-btn>
    </div>
</div>
@endif
