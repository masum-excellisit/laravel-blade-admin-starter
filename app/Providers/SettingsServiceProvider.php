<?php

namespace App\Providers;

use App\Models\Setting;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class SettingsServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // super-admin bypasses all permission checks
        Gate::before(fn ($user, $ability) => $user->hasRole('super-admin') ? true : null);

        if ($this->missingSettingsTable()) {
            View::share('settings', []);

            return;
        }

        $settings = Setting::all_cached();
        View::share('settings', $settings);

        $this->applyMailConfig($settings);
    }

    protected function missingSettingsTable(): bool
    {
        try {
            return ! Schema::hasTable('settings');
        } catch (\Throwable $e) {
            return true;
        }
    }

    protected function applyMailConfig(array $s): void
    {
        if (empty($s['mail_host'])) {
            return;
        }

        config([
            'mail.default' => 'smtp',
            'mail.mailers.smtp.host' => $s['mail_host'],
            'mail.mailers.smtp.port' => $s['mail_port'] ?? 587,
            'mail.mailers.smtp.username' => $s['mail_username'] ?? null,
            'mail.mailers.smtp.password' => $s['mail_password'] ?? null,
            'mail.mailers.smtp.encryption' => $s['mail_encryption'] ?? 'tls',
            'mail.from.address' => $s['mail_from_address'] ?? config('mail.from.address'),
            'mail.from.name' => $s['mail_from_name'] ?? ($s['site_name'] ?? config('mail.from.name')),
        ]);
    }
}
