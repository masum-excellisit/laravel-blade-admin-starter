@extends('layouts.admin')
@section('title', 'Settings')
@section('content')
<div x-data="{ tab: 'general' }">
<x-page-header title="Settings" subtitle="Configure your site, mail, theme, analytics, maintenance, and privacy options." />
<div class="flex gap-2 mb-6 border-b border-slate-200 dark:border-slate-700">
    @foreach(['general'=>'General','theme'=>'Theme','mail'=>'Mail','analytics'=>'Analytics','maintenance'=>'Maintenance','notifications'=>'Notifications','cookie'=>'Cookie'] as $key=>$label)
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
                <x-form.image name="site_logo" label="Logo" rounded="rounded-xl"
                    :current="($general['site_logo'] ?? false) ? \Illuminate\Support\Facades\Storage::disk('public')->url($general['site_logo']) : ''" />
                <x-form.image name="site_favicon" label="Favicon" rounded="rounded-lg"
                    :current="($general['site_favicon'] ?? false) ? \Illuminate\Support\Facades\Storage::disk('public')->url($general['site_favicon']) : ''" />
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

    <!-- ANALYTICS -->
    <div x-show="tab==='analytics'" x-cloak class="max-w-2xl">
        <x-card title="Tracking scripts">
            <div class="space-y-4">
                <x-form.input name="analytics_gtm_id" label="Google Tag Manager ID" :value="$analytics['analytics_gtm_id'] ?? ''" hint="Example: GTM-XXXXXXX" />
                <x-form.input name="analytics_ga4_id" label="GA4 measurement ID" :value="$analytics['analytics_ga4_id'] ?? ''" hint="Example: G-XXXXXXXXXX" />
                <x-form.input name="analytics_plausible_domain" label="Plausible domain" :value="$analytics['analytics_plausible_domain'] ?? ''" hint="Example: example.com" />
            </div>
        </x-card>
    </div>

    <!-- MAINTENANCE -->
    <div x-show="tab==='maintenance'" x-cloak class="max-w-2xl">
        <x-card title="Maintenance mode">
            <div class="space-y-4">
                <input type="hidden" name="maintenance_enabled" value="0">
                <label class="flex items-center gap-2 text-sm font-medium text-slate-700 dark:text-slate-200">
                    <input type="checkbox" name="maintenance_enabled" value="1" @checked(in_array(strtolower((string)($maintenance['maintenance_enabled'] ?? '0')), ['1','true','yes','on'], true)) class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                    Enable maintenance mode for public visitors
                </label>
                <x-form.input name="maintenance_headline" label="Headline" :value="$maintenance['maintenance_headline'] ?? ''" />
                <x-form.textarea name="maintenance_message" label="Message" :value="$maintenance['maintenance_message'] ?? ''" rows="3" />
            </div>
        </x-card>
    </div>

    <!-- NOTIFICATIONS -->
    <div x-show="tab==='notifications'" x-cloak class="max-w-2xl">
        <x-card title="Notifications">
            <div class="space-y-4">
                <x-form.input name="notify_contact_email" label="Notification email" :value="$notifications['notify_contact_email'] ?? ''" />
                <input type="hidden" name="notify_job_applications" value="0">
                <label class="flex items-center gap-2 text-sm font-medium text-slate-700 dark:text-slate-200">
                    <input type="checkbox" name="notify_job_applications" value="1" @checked(in_array(strtolower((string)($notifications['notify_job_applications'] ?? '0')), ['1','true','yes','on'], true)) class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                    Email job applications
                </label>
                <input type="hidden" name="notify_auto_reply" value="0">
                <label class="flex items-center gap-2 text-sm font-medium text-slate-700 dark:text-slate-200">
                    <input type="checkbox" name="notify_auto_reply" value="1" @checked(in_array(strtolower((string)($notifications['notify_auto_reply'] ?? '0')), ['1','true','yes','on'], true)) class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                    Send contact form auto-reply
                </label>
                <x-form.input name="notify_auto_reply_subject" label="Auto-reply subject" :value="$notifications['notify_auto_reply_subject'] ?? ''" />
                <x-form.textarea name="notify_auto_reply_body" label="Auto-reply body" :value="$notifications['notify_auto_reply_body'] ?? ''" rows="4" />
            </div>
        </x-card>
    </div>

    <!-- COOKIE -->
    <div x-show="tab==='cookie'" x-cloak class="max-w-2xl">
        <x-card title="Cookie banner">
            <div class="space-y-4">
                <input type="hidden" name="cookie_enabled" value="0">
                <label class="flex items-center gap-2 text-sm font-medium text-slate-700 dark:text-slate-200">
                    <input type="checkbox" name="cookie_enabled" value="1" @checked(in_array(strtolower((string)($cookie['cookie_enabled'] ?? '0')), ['1','true','yes','on'], true)) class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                    Show cookie banner
                </label>
                <x-form.textarea name="cookie_message" label="Message" :value="$cookie['cookie_message'] ?? ''" rows="3" />
                <x-form.input name="cookie_policy_url" label="Policy URL" :value="$cookie['cookie_policy_url'] ?? ''" />
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
