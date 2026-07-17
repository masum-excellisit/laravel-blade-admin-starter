<?php

namespace Tests\Feature;

use App\Models\Media;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CmsUpgradeFeaturesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_customer_can_register_and_login(): void
    {
        $this->post('/account/register', [
            'name' => 'Customer One',
            'email' => 'customer@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertRedirect('/account/profile');

        $customer = User::where('email', 'customer@example.com')->firstOrFail();

        $this->assertAuthenticatedAs($customer);
        $this->assertTrue($customer->isCustomer());
        $this->assertTrue($customer->status);

        $this->post('/account/logout')->assertRedirect('/');
        $this->assertGuest();

        $this->post('/account/login', [
            'email' => 'customer@example.com',
            'password' => 'password123',
        ])->assertRedirect('/account/profile');

        $this->assertAuthenticatedAs($customer);
    }

    public function test_media_index_still_returns_successful_response(): void
    {
        Media::create([
            'name' => 'Hero.jpg',
            'folder' => 'heroes',
            'path' => 'media/hero.jpg',
            'disk' => 'public',
            'mime' => 'image/jpeg',
            'size' => 1024,
        ]);

        $this->actingAs($this->admin())
            ->get(route('admin.media.index', ['folder' => 'heroes']))
            ->assertOk()
            ->assertSee('Hero.jpg')
            ->assertSee('heroes');
    }

    public function test_backup_download_returns_successful_response_for_admin(): void
    {
        $response = $this->actingAs($this->admin())
            ->post('/admin/backups/download');

        $response->assertOk();
        $response->assertHeader('content-disposition');
    }

    protected function admin(): User
    {
        return User::where('email', 'superadmin@yopmail.com')->firstOrFail();
    }
}
