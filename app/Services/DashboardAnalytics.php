<?php

namespace App\Services;

use App\Models\ContactMessage;
use App\Models\Faq;
use App\Models\FormSubmission;
use App\Models\JobApplication;
use App\Models\JobListing;
use App\Models\Media;
use App\Models\Page;
use App\Models\PortfolioItem;
use App\Models\Post;
use App\Models\Service;
use App\Models\TeamMember;
use App\Models\Testimonial;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

/**
 * Aggregates for the admin dashboard widgets.
 *
 * Every public method feeds exactly one widget partial, so deleting a widget
 * means deleting its partial + (optionally) the matching method here.
 * Nothing else in the app depends on this class.
 */
class DashboardAnalytics
{
    /** Seconds the whole payload is cached. Set to 0 to disable caching. */
    public const CACHE_TTL = 300;

    public const CACHE_KEY = 'admin.dashboard.analytics';

    /** Daily count maps keyed by model class, memoised per request. */
    protected array $daily = [];

    public function all(): array
    {
        $build = fn () => [
            'kpis' => $this->kpis(),
            'trend' => $this->trend(),
            'contentMix' => $this->contentMix(),
            'engagement' => $this->engagement(),
            'system' => $this->system(),
        ];

        return self::CACHE_TTL > 0
            ? Cache::remember(self::CACHE_KEY, self::CACHE_TTL, $build)
            : $build();
    }

    public static function flush(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /* ---------------------------------------------------------------- KPIs */

    public function kpis(): array
    {
        return [
            $this->kpi('Customers', User::customers()->count(), User::class, 'admin.customers.index', 'customers.view', 'users', 'var(--c-primary)', fn ($q) => $q->customers()),
            $this->kpi('Posts', Post::count(), Post::class, 'admin.posts.index', 'posts.view', 'pencil', 'var(--c-secondary)'),
            $this->kpi('Pages', Page::count(), Page::class, 'admin.pages.index', 'pages.view', 'document', '#0ea5e9'),
            $this->kpi('Media', Media::count(), Media::class, 'admin.media.index', 'media.view', 'photo', '#f59e0b'),
            $this->kpi('Messages', ContactMessage::where('read', false)->count(), ContactMessage::class, 'admin.messages.index', 'messages.view', 'inbox', 'var(--c-accent)'),
        ];
    }

    protected function kpi(string $label, int $total, string $model, string $route, ?string $can, string $icon, string $color, ?callable $scope = null): array
    {
        $map = $this->dailyMap($model, $scope);
        $spark = $this->lastDays($map, 30)['data'];
        $current = array_sum($spark);
        $previous = array_sum($this->windowSum($map, 60, 30));

        return [
            'label' => $label,
            'value' => $total,
            'route' => $route,
            'can' => $can,
            'icon' => $icon,
            'color' => $color,
            'spark' => $spark,
            'delta' => $this->percentChange($previous, $current),
            'new' => $current,
        ];
    }

    /* --------------------------------------------------------------- Trend */

    /** Multi-series growth chart with 30-day and 12-month views. */
    public function trend(): array
    {
        $series = [
            ['name' => 'Customers', 'color' => 'var(--c-primary)', 'model' => User::class, 'scope' => fn ($q) => $q->customers()],
            ['name' => 'Posts', 'color' => 'var(--c-secondary)', 'model' => Post::class, 'scope' => null],
            ['name' => 'Messages', 'color' => 'var(--c-accent)', 'model' => ContactMessage::class, 'scope' => null],
        ];

        $days = ['labels' => [], 'series' => []];
        $months = ['labels' => [], 'series' => []];

        foreach ($series as $s) {
            $map = $this->dailyMap($s['model'], $s['scope']);
            $d = $this->lastDays($map, 30);
            $m = $this->lastMonths($map, 12);
            $days['labels'] = $d['labels'];
            $months['labels'] = $m['labels'];
            $days['series'][] = ['name' => $s['name'], 'color' => $s['color'], 'data' => $d['data']];
            $months['series'][] = ['name' => $s['name'], 'color' => $s['color'], 'data' => $m['data']];
        }

        return ['days' => $days, 'months' => $months];
    }

    /* ---------------------------------------------------------- Content mix */

    public function contentMix(): array
    {
        $sources = [
            ['label' => 'Posts', 'model' => Post::class, 'color' => 'var(--c-primary)'],
            ['label' => 'Pages', 'model' => Page::class, 'color' => 'var(--c-secondary)'],
            ['label' => 'Services', 'model' => Service::class, 'color' => '#0ea5e9'],
            ['label' => 'Portfolio', 'model' => PortfolioItem::class, 'color' => '#10b981'],
            ['label' => 'FAQs', 'model' => Faq::class, 'color' => '#f59e0b'],
            ['label' => 'Team', 'model' => TeamMember::class, 'color' => '#ef4444'],
            ['label' => 'Testimonials', 'model' => Testimonial::class, 'color' => 'var(--c-accent)'],
            ['label' => 'Jobs', 'model' => JobListing::class, 'color' => '#64748b'],
        ];

        $out = [];
        foreach ($sources as $s) {
            $count = $this->safeCount($s['model']);
            if ($count > 0) {
                $out[] = ['label' => $s['label'], 'value' => $count, 'color' => $s['color']];
            }
        }

        return $out;
    }

    /* ----------------------------------------------------------- Engagement */

    /** Inbound volume per week for the last 8 weeks. */
    public function engagement(): array
    {
        $sources = [
            ['name' => 'Messages', 'color' => 'var(--c-primary)', 'model' => ContactMessage::class],
            ['name' => 'Form entries', 'color' => 'var(--c-secondary)', 'model' => FormSubmission::class],
            ['name' => 'Applications', 'color' => 'var(--c-accent)', 'model' => JobApplication::class],
        ];

        $labels = [];
        $series = [];

        foreach ($sources as $s) {
            $weeks = $this->lastWeeks($this->dailyMap($s['model']), 8);
            $labels = $weeks['labels'];
            $series[] = ['name' => $s['name'], 'color' => $s['color'], 'data' => $weeks['data']];
        }

        $total = array_sum(array_merge(...array_column($series, 'data')));

        return ['labels' => $labels, 'series' => $series, 'total' => $total];
    }

    /* --------------------------------------------------------------- System */

    public function system(): array
    {
        $published = Post::where('status', 'published')->count();
        $posts = Post::count();

        return [
            'published' => $published,
            'drafts' => max($posts - $published, 0),
            'posts' => $posts,
            'mediaCount' => $this->safeCount(Media::class),
            'mediaBytes' => (int) (Schema::hasTable('media') ? Media::sum('size') : 0),
            'php' => PHP_VERSION,
            'laravel' => app()->version(),
            'env' => app()->environment(),
            'debug' => (bool) config('app.debug'),
            'cache' => config('cache.default'),
            'queue' => config('queue.default'),
        ];
    }

    /* ---------------------------------------------------------- Series math */

    /** ['Y-m-d' => count] for the trailing 12 months, DB-agnostic. */
    protected function dailyMap(string $model, ?callable $scope = null): array
    {
        $key = $model.($scope ? ':scoped' : '');
        if (isset($this->daily[$key])) {
            return $this->daily[$key];
        }

        if (! $this->tableExists($model)) {
            return $this->daily[$key] = [];
        }

        $query = $model::query()->where('created_at', '>=', now()->startOfDay()->subDays(364));
        if ($scope) {
            $scope($query);
        }

        return $this->daily[$key] = $query
            ->selectRaw('DATE(created_at) as d, COUNT(*) as c')
            ->groupBy('d')
            ->pluck('c', 'd')
            ->map(fn ($c) => (int) $c)
            ->all();
    }

    protected function lastDays(array $map, int $days): array
    {
        $labels = [];
        $data = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $day = now()->startOfDay()->subDays($i);
            $labels[] = $day->format('M j');
            $data[] = $map[$day->format('Y-m-d')] ?? 0;
        }

        return compact('labels', 'data');
    }

    protected function lastMonths(array $map, int $months): array
    {
        $buckets = [];
        $labels = [];
        for ($i = $months - 1; $i >= 0; $i--) {
            $month = now()->startOfMonth()->subMonths($i);
            $buckets[$month->format('Y-m')] = 0;
            $labels[] = $month->format('M');
        }

        foreach ($map as $date => $count) {
            $bucket = substr((string) $date, 0, 7);
            if (array_key_exists($bucket, $buckets)) {
                $buckets[$bucket] += $count;
            }
        }

        return ['labels' => $labels, 'data' => array_values($buckets)];
    }

    protected function lastWeeks(array $map, int $weeks): array
    {
        $labels = [];
        $data = [];
        for ($i = $weeks - 1; $i >= 0; $i--) {
            $start = now()->startOfWeek()->subWeeks($i);
            $labels[] = $start->format('M j');
            $sum = 0;
            for ($d = 0; $d < 7; $d++) {
                $sum += $map[$start->copy()->addDays($d)->format('Y-m-d')] ?? 0;
            }
            $data[] = $sum;
        }

        return compact('labels', 'data');
    }

    /** Counts inside the window that started $offset days ago and ran $length days. */
    protected function windowSum(array $map, int $offset, int $length): array
    {
        $out = [];
        for ($i = $offset - 1; $i >= $offset - $length; $i--) {
            $out[] = $map[now()->startOfDay()->subDays($i)->format('Y-m-d')] ?? 0;
        }

        return $out;
    }

    protected function percentChange(int $previous, int $current): ?float
    {
        if ($previous === 0) {
            return $current === 0 ? 0.0 : null; // null renders as "new"
        }

        return round((($current - $previous) / $previous) * 100, 1);
    }

    protected function safeCount(string $model): int
    {
        return $this->tableExists($model) ? $model::count() : 0;
    }

    protected function tableExists(string $model): bool
    {
        static $cache = [];

        return $cache[$model] ??= Schema::hasTable((new $model)->getTable());
    }
}
