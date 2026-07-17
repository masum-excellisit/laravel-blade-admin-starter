<?php

namespace App\Http\Middleware;

use App\Models\Redirect;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HandleRedirects
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($this->shouldSkip($request)) {
            return $next($request);
        }

        $path = '/'.ltrim($request->path(), '/');
        $path = $path === '//' ? '/' : $path;

        $redirect = Redirect::query()
            ->where('is_active', true)
            ->whereIn('from_path', [$path, ltrim($path, '/')])
            ->first();

        if (! $redirect) {
            return $next($request);
        }

        $redirect->increment('hits');

        return redirect($redirect->to_url, $redirect->status_code);
    }

    private function shouldSkip(Request $request): bool
    {
        return $request->is('admin')
            || $request->is('admin/*')
            || $request->is('assets/*')
            || $request->is('build/*')
            || $request->is('css/*')
            || $request->is('js/*')
            || $request->is('images/*')
            || $request->is('storage/*')
            || $request->is('favicon.ico');
    }
}
