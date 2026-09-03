<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Support\Activity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class SettingController extends Controller
{
    public function edit()
    {
        return view('admin.settings.edit', [
            'general' => Setting::group('general'),
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
        Activity::log('updated', null, 'Settings updated');

        return back()->with('success', 'Settings saved.');
    }

    public function testMail(Request $request)
    {
        abort_unless($request->user()->can('settings.edit'), 403);
        $request->validate(['test_email' => ['required', 'email']]);

        $mail = Setting::group('mail');
        $host = trim((string) ($mail['mail_host'] ?? ''));

        if ($host === '') {
            return back()
                ->withInput()
                ->with('error', 'Add an SMTP host in Mail settings and save before sending a test email.');
        }

        $siteName = Setting::get('site_name', config('app.name'));

        config([
            'mail.default' => 'smtp',
            'mail.mailers.smtp.host' => $host,
            'mail.mailers.smtp.port' => (int) ($mail['mail_port'] ?? 587),
            'mail.mailers.smtp.username' => $mail['mail_username'] ?? null,
            'mail.mailers.smtp.password' => $mail['mail_password'] ?? null,
            'mail.mailers.smtp.encryption' => ($mail['mail_encryption'] ?? 'tls') === 'null'
                ? null
                : ($mail['mail_encryption'] ?? 'tls'),
            'mail.from.address' => $mail['mail_from_address'] ?? config('mail.from.address'),
            'mail.from.name' => $mail['mail_from_name'] ?? $siteName,
        ]);

        try {
            Mail::mailer('smtp')->raw(
                'This is a test email from '.$siteName.'. Your mail settings work!',
                function ($m) use ($request) {
                    $m->to($request->test_email)->subject('Test email');
                }
            );
        } catch (\Throwable $e) {
            return back()->with('error', 'Mail failed: '.$e->getMessage());
        }

        return back()->with('success', 'Test email sent to '.$request->test_email);
    }
}
