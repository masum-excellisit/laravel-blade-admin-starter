<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckMaintenanceMode
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $this->enabled() || $request->is('admin') || $request->is('admin/*') || $this->canBypass($request)) {
            return $next($request);
        }

        return response()->view('site.maintenance', [
            'headline' => Setting::get('maintenance_headline', 'We will be right back'),
            'message' => Setting::get('maintenance_message', 'We are making improvements and will be back shortly.'),
        ], 503);
    }

    private function enabled(): bool
    {
        $value = Setting::get('maintenance_enabled', '0');

        return $value === true
            || $value === 1
            || in_array(strtolower((string) $value), ['1', 'true', 'yes', 'on'], true);
    }

    private function canBypass(Request $request): bool
    {
        $user = $request->user();

        if (! $user) {
            return false;
        }

        if (method_exists($user, 'isAdmin') && $user->isAdmin()) {
            return true;
        }

        return method_exists($user, 'roles') && $user->roles()->exists();
    }
}
