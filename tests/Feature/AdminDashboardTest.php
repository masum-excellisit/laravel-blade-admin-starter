<?php

namespace Tests\Feature;

use App\Models\ContactMessage;
use App\Models\Post;
use App\Models\User;
use App\Services\DashboardAnalytics;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        DashboardAnalytics::flush();
    }

    protected function admin(): User
    {
        return User::where('email', 'superadmin@yopmail.com')->firstOrFail();
    }

    public function test_dashboard_renders_every_widget(): void
    {
        $this->actingAs($this->admin())
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('css/admin-dashboard.css', false)
            ->assertSee('ez-kpi', false)          // KPI cards
            ->assertSee('ez-chart__line', false)  // growth area chart
            ->assertSee('ez-donut__seg', false)   // content mix donut
            ->assertSee('ez-bars__bar', false)    // inbound bar chart
            ->assertSee('ez-gauge__value', false) // health gauge
            ->assertSee('Recent posts')
            ->assertSee('New customers')
            ->assertSee('Activity');
    }

    public function test_analytics_series_have_the_expected_shape(): void
    {
        $data = app(DashboardAnalytics::class)->all();

        $this->assertCount(5, $data['kpis']);
        $this->assertCount(30, $data['trend']['days']['labels']);
        $this->assertCount(12, $data['trend']['months']['labels']);
        $this->assertCount(8, $data['engagement']['labels']);

        foreach ($data['trend']['days']['series'] as $series) {
            $this->assertCount(30, $series['data']);
        }

        foreach ($data['kpis'] as $kpi) {
            $this->assertCount(30, $kpi['spark']);
            $this->assertIsInt($kpi['value']);
        }
    }

    public function test_analytics_counts_recent_records(): void
    {
        $before = app(DashboardAnalytics::class)->kpis();
        $beforePosts = collect($before)->firstWhere('label', 'Posts');

        foreach (range(1, 3) as $i) {
            Post::create([
                'title' => "Metric post {$i}",
                'slug' => "metric-post-{$i}",
                'body' => 'Body',
                'status' => 'draft',
            ])->forceFill(['created_at' => now()->subDays(2)])->save();
        }

        $after = app(DashboardAnalytics::class)->kpis();
        $afterPosts = collect($after)->firstWhere('label', 'Posts');

        $this->assertSame($beforePosts['new'] + 3, $afterPosts['new']);
        $this->assertSame(array_sum($afterPosts['spark']), $afterPosts['new']);
    }

    public function test_unread_message_kpi_tracks_the_inbox(): void
    {
        ContactMessage::query()->update(['read' => true]);
        DashboardAnalytics::flush();

        $unread = collect(app(DashboardAnalytics::class)->kpis())->firstWhere('label', 'Messages');

        $this->assertSame(0, $unread['value']);
    }
}
