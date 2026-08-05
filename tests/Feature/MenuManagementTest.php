<?php

namespace Tests\Feature;

use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MenuManagementTest extends TestCase
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

    public function test_can_add_url_menu_item(): void
    {
        $menu = Menu::where('location', 'header')->firstOrFail();

        $this->actingAs($this->admin())
            ->post(route('admin.menus.items.store', $menu), [
                'label' => 'External Docs',
                'type' => 'url',
                'value' => 'https://example.com/docs',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('menu_items', [
            'menu_id' => $menu->id,
            'label' => 'External Docs',
            'type' => 'url',
            'value' => 'https://example.com/docs',
        ]);
    }

    public function test_cannot_attach_parent_from_another_menu(): void
    {
        $header = Menu::where('location', 'header')->firstOrFail();
        $footer = Menu::where('location', 'footer')->firstOrFail();
        $foreignParent = $footer->items()->whereNull('parent_id')->firstOrFail();

        $this->actingAs($this->admin())
            ->post(route('admin.menus.items.store', $header), [
                'label' => 'Orphan child',
                'type' => 'url',
                'value' => 'https://example.com',
                'parent_id' => $foreignParent->id,
            ])
            ->assertSessionHasErrors('parent_id');

        $this->assertDatabaseMissing('menu_items', [
            'menu_id' => $header->id,
            'label' => 'Orphan child',
        ]);
    }

    public function test_location_must_be_unique_on_create(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.menus.store'), [
                'name' => 'Another Header',
                'location' => 'header',
            ])
            ->assertSessionHasErrors('location');
    }

    public function test_location_must_be_unique_on_update(): void
    {
        $header = Menu::where('location', 'header')->firstOrFail();
        $footer = Menu::where('location', 'footer')->firstOrFail();

        $this->actingAs($this->admin())
            ->put(route('admin.menus.update', $footer), [
                'name' => $footer->name,
                'location' => 'header',
            ])
            ->assertSessionHasErrors('location');

        $this->assertSame('header', $header->fresh()->location);
        $this->assertSame('footer', $footer->fresh()->location);
    }

    public function test_edit_form_does_not_submit_duplicate_value_fields(): void
    {
        $menu = Menu::where('location', 'header')->firstOrFail();

        $html = $this->actingAs($this->admin())
            ->get(route('admin.menus.edit', $menu))
            ->assertOk()
            ->getContent();

        // Value fields must live inside Alpine x-if templates so inactive
        // types are not submitted (x-show leaves them in the DOM).
        $this->assertStringContainsString('x-if="type === \'url\'"', $html);
        $this->assertStringContainsString('x-if="type === \'page\'"', $html);
        $this->assertStringContainsString('x-if="type === \'route\'"', $html);
        $this->assertStringNotContainsString('x-show="type===\'url\'"', $html);
    }

    public function test_menu_item_url_resolves_by_type(): void
    {
        $menu = Menu::where('location', 'header')->firstOrFail();

        $urlItem = MenuItem::create([
            'menu_id' => $menu->id,
            'label' => 'URL',
            'type' => 'url',
            'value' => 'https://example.com',
            'order' => 99,
        ]);
        $pageItem = MenuItem::create([
            'menu_id' => $menu->id,
            'label' => 'Page',
            'type' => 'page',
            'value' => 'privacy-policy',
            'order' => 100,
        ]);
        $routeItem = MenuItem::create([
            'menu_id' => $menu->id,
            'label' => 'Route',
            'type' => 'route',
            'value' => 'contact',
            'order' => 101,
        ]);

        $this->assertSame('https://example.com', $urlItem->url());
        $this->assertSame(url('/privacy-policy'), $pageItem->url());
        $this->assertSame(route('contact'), $routeItem->url());
    }

    public function test_public_site_renders_menu_for_location(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Services')
            ->assertSee('Contact');
    }

    public function test_can_update_menu_item(): void
    {
        $menu = Menu::where('location', 'header')->firstOrFail();
        $item = $menu->items()->whereNull('parent_id')->firstOrFail();

        $this->actingAs($this->admin())
            ->put(route('admin.menus.items.update', $item), [
                'label' => 'Updated Label',
                'type' => 'url',
                'value' => 'https://example.com/updated',
                'parent_id' => null,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('menu_items', [
            'id' => $item->id,
            'label' => 'Updated Label',
            'type' => 'url',
            'value' => 'https://example.com/updated',
        ]);
    }

    public function test_update_rejects_parent_from_another_menu(): void
    {
        $header = Menu::where('location', 'header')->firstOrFail();
        $footer = Menu::where('location', 'footer')->firstOrFail();
        $item = $header->items()->whereNull('parent_id')->firstOrFail();
        $foreignParent = $footer->items()->whereNull('parent_id')->firstOrFail();

        $this->actingAs($this->admin())
            ->put(route('admin.menus.items.update', $item), [
                'label' => $item->label,
                'type' => 'url',
                'value' => 'https://example.com',
                'parent_id' => $foreignParent->id,
            ])
            ->assertSessionHasErrors('parent_id');
    }

    public function test_edit_page_exposes_item_update_actions(): void
    {
        $menu = Menu::where('location', 'header')->firstOrFail();
        $item = $menu->items()->whereNull('parent_id')->firstOrFail();

        $html = $this->actingAs($this->admin())
            ->get(route('admin.menus.edit', $menu))
            ->assertOk()
            ->assertSee(route('admin.menus.items.update', $item), false)
            ->getContent();

        $this->assertMatchesRegularExpression('/\$dispatch\(\'menu-edit\',\s*'.(int) $item->id.'\)/', $html);
        $this->assertStringNotContainsString('id]))">Edit', $html);
        $this->assertStringContainsString('>Edit</button>', $html);
    }
}
