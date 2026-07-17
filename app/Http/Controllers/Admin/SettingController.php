<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class SettingController extends Controller
{
    public function edit()
    {
        return view('admin.settings.edit', [
            'general' => Setting::group('general'),
            'theme' => Setting::group('theme'),
            'mail' => Setting::group('mail'),
            'analytics' => Setting::group('analytics'),
            'maintenance' => Setting::group('maintenance'),
            'notifications' => Setting::group('notifications'),
            'cookie' => Setting::group('cookie'),
        ]);
    }

    public function update(Request $request)
    {
        abort_unless($request->user()->can('settings.edit'), 403);

        $keys = [
            'general' => ['site_name', 'site_tagline', 'contact_email', 'contact_phone', 'contact_address', 'social_twitter', 'social_github', 'social_linkedin'],
            'theme' => ['theme_primary', 'theme_secondary', 'theme_accent', 'theme_sidebar', 'theme_mode'],
            'mail' => ['mail_host', 'mail_port', 'mail_username', 'mail_password', 'mail_encryption', 'mail_from_address', 'mail_from_name'],
            'analytics' => ['analytics_ga4_id', 'analytics_gtm_id', 'analytics_plausible_domain'],
            'maintenance' => ['maintenance_enabled', 'maintenance_headline', 'maintenance_message'],
            'notifications' => ['notify_contact_email', 'notify_job_applications', 'notify_auto_reply', 'notify_auto_reply_subject', 'notify_auto_reply_body'],
            'cookie' => ['cookie_enabled', 'cookie_message', 'cookie_policy_url'],
        ];

        foreach ($keys as $group => $groupKeys) {
            foreach ($groupKeys as $key) {
                if ($request->has($key)) {
                    Setting::put($key, $request->input($key), $group);
                }
            }
        }

        foreach (['site_logo', 'site_favicon'] as $imageKey) {
            if ($request->hasFile($imageKey)) {
                $path = $request->file($imageKey)->store('branding', 'public');
                Setting::put($imageKey, $path, 'general', 'image');
            }
        }

        Setting::flush();

        return back()->with('success', 'Settings saved.');
    }

    public function testMail(Request $request)
    {
        abort_unless($request->user()->can('settings.edit'), 403);
        $request->validate(['test_email' => ['required', 'email']]);

        try {
            Mail::raw('This is a test email from '.($settings['site_name'] ?? config('app.name')).'. Your mail settings work!', function ($m) use ($request) {
                $m->to($request->test_email)->subject('Test email');
            });
        } catch (\Throwable $e) {
            return back()->with('error', 'Mail failed: '.$e->getMessage());
        }

        return back()->with('success', 'Test email sent to '.$request->test_email);
    }
}
