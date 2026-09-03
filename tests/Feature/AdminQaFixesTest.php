<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminQaFixesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    protected function admin(): User
    {
        return User::where('email', 'superadmin@yopmail.com')->firstOrFail();
    }

    public function test_categories_create_redirects_to_index(): void
    {
        $this->actingAs($this->admin())
            ->get('/admin/categories/create')
            ->assertRedirect(route('admin.categories.index'));
    }

    public function test_branded_404_page_renders(): void
    {
        $this->actingAs($this->admin())
            ->get('/admin/this-page-definitely-missing-xyz')
            ->assertNotFound()
            ->assertSee('Page not found')
            ->assertDontSee('AbstractRouteCollection');
    }

    public function test_activity_log_records_auth_and_crud(): void
    {
        $admin = $this->admin();

        $this->post(route('admin.login.attempt'), [
            'email' => $admin->email,
            'password' => '12345678',
        ])->assertRedirect(route('admin.dashboard'));

        $this->assertDatabaseHas('activity_logs', [
            'action' => 'login',
            'user_id' => $admin->id,
        ]);

        $this->actingAs($admin);

        $category = Category::create(['name' => 'QA Cat', 'slug' => 'qa-cat']);

        $this->assertTrue(
            ActivityLog::where('action', 'created')
                ->where('subject_type', $category->getMorphClass())
                ->where('subject_id', $category->id)
                ->exists()
        );

        $post = Post::create([
            'title' => 'QA Post',
            'slug' => 'qa-post',
            'status' => 'draft',
            'author_id' => $admin->id,
        ]);

        $this->assertTrue(
            ActivityLog::where('action', 'created')
                ->where('subject_type', $post->getMorphClass())
                ->where('subject_id', $post->id)
                ->exists()
        );
    }

    public function test_forgot_password_redirects_authenticated_admin_to_dashboard(): void
    {
        $this->actingAs($this->admin())
            ->get(route('admin.password.request'))
            ->assertRedirect(route('admin.dashboard'));
    }

    public function test_mail_test_blocked_without_host(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.settings.test-mail'), ['test_email' => 'qa@example.com'])
            ->assertRedirect()
            ->assertSessionHas('error');
    }

    public function test_posts_search_empty_state_copy(): void
    {
        $this->actingAs($this->admin())
            ->get(route('admin.posts.index', ['search' => 'zzzz-no-match-zzzz']))
            ->assertOk()
            ->assertSee('No posts match your filters')
            ->assertSee('Clear filters')
            ->assertDontSee('No posts yet.');
    }
}
