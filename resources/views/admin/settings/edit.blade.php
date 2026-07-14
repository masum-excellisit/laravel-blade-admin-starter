@extends('layouts.admin')
@section('title', 'Settings')
@section('content')
<div x-data="{ tab: 'general' }">
<x-page-header title="Settings" subtitle="Configure your site, mail, and theme." />
<div class="flex gap-2 mb-6 border-b border-slate-200 dark:border-slate-700">
    @foreach(['general'=>'General','theme'=>'Theme','mail'=>'Mail'] as $key=>$label)
    <button x-on:click="tab='{{ $key }}'" :class="tab==='{{ $key }}' ? 'border-primary text-primary' : 'border-transparent text-slate-500'" class="px-4 py-2.5 -mb-px border-b-2 font-medium text-sm">{{ $label }}</button>
    @endforeach
</div>

<form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data">@csrf @method('PUT')
    <!-- GENERAL -->
    <div x-show="tab==='general'" class="grid grid-cols-1 lg:grid-cols-2 gap-6 max-w-4xl">
        <x-card title="Site">
            <div class="space-y-4">
                <x-form.input name="site_name" label="Site name" :value="$general['site_name'] ?? ''" />
                <x-form.textarea name="site_tagline" label="Tagline" :value="$general['site_tagline'] ?? ''" rows="2" />
                <x-form.input name="site_logo" type="file" label="Logo" accept="image/*" />
                @if($general['site_logo'] ?? false)<img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($general['site_logo']) }}" class="h-10">@endif
                <x-form.input name="site_favicon" type="file" label="Favicon" accept="image/*" />
            </div>
        </x-card>
        <x-card title="Contact & social">
            <div class="space-y-4">
                <x-form.input name="contact_email" label="Contact email" :value="$general['contact_email'] ?? ''" />
                <x-form.input name="contact_phone" label="Phone" :value="$general['contact_phone'] ?? ''" />
                <x-form.input name="contact_address" label="Address" :value="$general['contact_address'] ?? ''" />
                <x-form.input name="social_twitter" label="Twitter/X" :value="$general['social_twitter'] ?? ''" />
                <x-form.input name="social_github" label="GitHub" :value="$general['social_github'] ?? ''" />
                <x-form.input name="social_linkedin" label="LinkedIn" :value="$general['social_linkedin'] ?? ''" />
            </div>
        </x-card>
    </div>

    <!-- THEME -->
    <div x-show="tab==='theme'" x-cloak class="max-w-2xl">
        <x-card title="Theme colours">
            <div class="grid grid-cols-2 gap-5">
                @foreach(['theme_primary'=>'Primary','theme_secondary'=>'Secondary','theme_accent'=>'Accent','theme_sidebar'=>'Sidebar'] as $k=>$l)
                <div>
                    <x-form.label>{{ $l }}</x-form.label>
                    <input type="color" name="{{ $k }}" value="{{ $theme[$k] ?? '#6366f1' }}" class="h-11 w-full rounded-xl border border-slate-300 dark:border-slate-600">
                </div>
                @endforeach
                <x-form.select name="theme_mode" label="Default mode" :options="['light'=>'Light','dark'=>'Dark']" :selected="$theme['theme_mode'] ?? 'light'" />
            </div>
            <p class="mt-4 text-xs text-slate-400">Colours drive CSS variables across both the admin panel and public site.</p>
        </x-card>
    </div>

    <!-- MAIL -->
    <div x-show="tab==='mail'" x-cloak class="max-w-2xl space-y-6">
        <x-card title="SMTP">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <x-form.input name="mail_host" label="Host" :value="$mail['mail_host'] ?? ''" />
                <x-form.input name="mail_port" label="Port" :value="$mail['mail_port'] ?? '587'" />
                <x-form.input name="mail_username" label="Username" :value="$mail['mail_username'] ?? ''" />
                <x-form.input name="mail_password" type="password" label="Password" :value="$mail['mail_password'] ?? ''" />
                <x-form.select name="mail_encryption" label="Encryption" :options="['tls'=>'TLS','ssl'=>'SSL','null'=>'None']" :selected="$mail['mail_encryption'] ?? 'tls'" />
                <x-form.input name="mail_from_address" label="From address" :value="$mail['mail_from_address'] ?? ''" />
                <x-form.input name="mail_from_name" label="From name" :value="$mail['mail_from_name'] ?? ''" />
            </div>
        </x-card>
    </div>

    <div class="mt-6 flex gap-2 max-w-4xl"><x-btn type="submit">Save settings</x-btn></div>
</form>

<div x-show="tab==='mail'" x-cloak class="max-w-2xl mt-6">
    <x-card title="Send test email">
        <form method="POST" action="{{ route('admin.settings.test-mail') }}" class="flex gap-2 items-end">@csrf
            <div class="flex-1"><x-form.input name="test_email" type="email" label="Recipient" required /></div>
            <x-btn variant="outline" type="submit">Send test</x-btn>
        </form>
    </x-card>
</div>
</div>
@endsection
