<?php

namespace Tests\Feature;

use App\Models\Menu;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPanelEnhancementsTest extends TestCase
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

    public function test_public_pages_render(): void
    {
        $this->get(route('home'))->assertOk();
        $this->get(route('about'))->assertOk();
        $this->get(route('services.index'))->assertOk();
        $this->get(route('how-it-works'))->assertOk();
        $this->get(route('careers'))->assertOk();
        $this->get(route('contact'))->assertOk();
        $this->get(route('jobs.index'))->assertOk();
    }

    public function test_admin_modules_and_cms_render(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk();
        $this->actingAs($admin)->get(route('admin.cms.index'))->assertOk();
        $this->actingAs($admin)->get(route('admin.cms.edit', 'home'))->assertOk();
        $this->actingAs($admin)->get(route('admin.services.index'))->assertOk();
        $this->actingAs($admin)->get(route('admin.testimonials.index'))->assertOk();
        $this->actingAs($admin)->get(route('admin.jobs.index'))->assertOk();
        $this->actingAs($admin)->get(route('admin.job-applications.index'))->assertOk();
        $this->actingAs($admin)->get(route('admin.pages.index'))->assertOk();
        $this->actingAs($admin)->get(route('admin.menus.index'))->assertOk();
        $this->actingAs($admin)->get(route('admin.menus.edit', Menu::first()))->assertOk();
        $this->actingAs($admin)->get(route('admin.users.index'))->assertOk();
        $this->actingAs($admin)->get(route('admin.settings.edit'))->assertOk();
    }

    public function test_pages_list_supports_search_and_sort(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->get(route('admin.pages.index', ['search' => 'About', 'sort' => 'title', 'direction' => 'asc']))
            ->assertOk()
            ->assertSee('About');
    }

    public function test_bulk_delete_pages(): void
    {
        $admin = $this->admin();
        $page = \App\Models\Page::first();

        $this->actingAs($admin)
            ->post(route('admin.pages.bulk'), [
                'ids' => [$page->id],
                'action' => 'delete',
            ])
            ->assertRedirect();

        $this->assertDatabaseMissing('pages', ['id' => $page->id]);
    }

    public function test_cms_update_persists_section_fields(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->put(route('admin.cms.update', 'home'), [
                'sections' => [
                    'hero' => [
                        'headline' => 'Updated headline from test',
                        'subheadline' => 'Updated sub',
                    ],
                    'features' => [
                        'title' => 'Features title',
                    ],
                ],
            ])
            ->assertRedirect();

        $this->assertEquals(
            'Updated headline from test',
            \App\Models\CmsContent::getField('home', 'hero', 'headline')
        );
    }

    public function test_menu_reorder(): void
    {
        $admin = $this->admin();
        $menu = Menu::where('location', 'header')->firstOrFail();
        $ids = $menu->items()->orderBy('order')->pluck('id')->reverse()->values()->all();

        $this->actingAs($admin)
            ->postJson(route('admin.menus.reorder', $menu), ['order' => $ids])
            ->assertOk()
            ->assertJson(['ok' => true]);
    }

    public function test_sidebar_order_keeps_access_control_below_settings(): void
    {
        $html = $this->actingAs($this->admin())
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->getContent();

        $settingsPos = strpos($html, '>Settings</span>');
        $usersPos = strpos($html, '>Admin Users</span>');
        $rolesPos = strpos($html, '>Roles</span>');
        $servicesPos = strpos($html, '>Services</span>');

        $this->assertNotFalse($settingsPos);
        $this->assertNotFalse($usersPos);
        $this->assertNotFalse($rolesPos);
        $this->assertNotFalse($servicesPos);
        $this->assertTrue($servicesPos < $settingsPos, 'Services should appear before Settings');
        $this->assertTrue($settingsPos < $usersPos, 'Settings should appear before Admin Users');
        $this->assertTrue($usersPos < $rolesPos, 'Admin Users should appear before Roles');
    }
}
