<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            // general
            ['general', 'site_name', 'Blade Admin Starter', 'text'],
            ['general', 'site_tagline', 'A premium Laravel starter kit for building anything.', 'text'],
            ['general', 'site_logo', '', 'image'],
            ['general', 'site_favicon', '', 'image'],
            ['general', 'contact_email', 'hello@example.com', 'text'],
            ['general', 'contact_phone', '+1 (555) 123-4567', 'text'],
            ['general', 'contact_address', '123 Market Street, San Francisco, CA', 'text'],
            ['general', 'social_twitter', 'https://twitter.com', 'text'],
            ['general', 'social_github', 'https://github.com', 'text'],
            ['general', 'social_linkedin', 'https://linkedin.com', 'text'],
            // theme
            ['theme', 'theme_primary', '#6366f1', 'color'],
            ['theme', 'theme_secondary', '#8b5cf6', 'color'],
            ['theme', 'theme_accent', '#ec4899', 'color'],
            ['theme', 'theme_sidebar', '#0f172a', 'color'],
            ['theme', 'theme_mode', 'light', 'text'],
            // mail
            ['mail', 'mail_host', '', 'text'],
            ['mail', 'mail_port', '587', 'text'],
            ['mail', 'mail_username', '', 'text'],
            ['mail', 'mail_password', '', 'password'],
            ['mail', 'mail_encryption', 'tls', 'text'],
            ['mail', 'mail_from_address', 'hello@example.com', 'text'],
            ['mail', 'mail_from_name', 'Blade Admin Starter', 'text'],
            // analytics
            ['analytics', 'analytics_ga4_id', '', 'text'],
            ['analytics', 'analytics_gtm_id', '', 'text'],
            ['analytics', 'analytics_plausible_domain', '', 'text'],
            // maintenance
            ['maintenance', 'maintenance_enabled', '0', 'boolean'],
            ['maintenance', 'maintenance_message', 'We are making improvements and will be back shortly.', 'textarea'],
            ['maintenance', 'maintenance_headline', 'We will be right back', 'text'],
            // cookie
            ['cookie', 'cookie_enabled', '1', 'boolean'],
            ['cookie', 'cookie_message', 'We use cookies to improve your browsing experience.', 'textarea'],
            ['cookie', 'cookie_policy_url', '', 'text'],
        ];

        foreach ($defaults as [$group, $key, $value, $type]) {
            Setting::firstOrCreate(['key' => $key], ['group' => $group, 'value' => $value, 'type' => $type]);
        }

        $contactEmail = Setting::where('key', 'contact_email')->value('value') ?? '';
        $notificationDefaults = [
            ['notifications', 'notify_contact_email', $contactEmail, 'text'],
            ['notifications', 'notify_job_applications', '1', 'boolean'],
            ['notifications', 'notify_auto_reply', '0', 'boolean'],
            ['notifications', 'notify_auto_reply_subject', 'Thanks for contacting us', 'text'],
            ['notifications', 'notify_auto_reply_body', 'Thanks for reaching out. We received your message and will respond soon.', 'textarea'],
        ];

        foreach ($notificationDefaults as [$group, $key, $value, $type]) {
            Setting::firstOrCreate(['key' => $key], ['group' => $group, 'value' => $value, 'type' => $type]);
        }

        Setting::flush();
    }
}
