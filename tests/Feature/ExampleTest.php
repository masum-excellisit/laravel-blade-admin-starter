<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_the_public_home_page_loads(): void
    {
        $this->get('/')->assertStatus(200);
    }

    public function test_the_admin_login_page_loads(): void
    {
        $this->get('/admin/login')->assertStatus(200);
    }

    public function test_guests_are_redirected_from_the_admin(): void
    {
        $this->get('/admin')->assertRedirect('/admin/login');
    }

    public function test_super_admin_can_reach_the_dashboard(): void
    {
        $admin = User::role('super-admin')->first();

        $this->actingAs($admin)->get('/admin')->assertStatus(200);
    }

    public function test_editor_is_denied_the_users_module(): void
    {
        $editor = User::role('editor')->first();

        $this->actingAs($editor)->get('/admin/users')->assertStatus(403);
    }
}
