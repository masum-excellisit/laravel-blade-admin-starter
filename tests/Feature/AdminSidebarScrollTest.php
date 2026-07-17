<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminSidebarScrollTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_admin_layout_includes_improved_sidebar_scroll(): void
    {
        $admin = User::where('email', 'superadmin@yopmail.com')->firstOrFail();

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('admin-sidebar-scroll-shell', false)
            ->assertSee('admin-sidebar-scroll', false)
            ->assertSee('sidebarScroll', false);
    }
}
