<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RolePermissionsUiTest extends TestCase
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

    public function test_permissions_index_uses_module_cards(): void
    {
        $response = $this->actingAs($this->admin())
            ->get(route('admin.permissions.index'))
            ->assertOk()
            ->assertSee('Total permissions')
            ->assertSee('Modules')
            ->assertSee('cms.create')
            ->assertSee('Create');

        $html = $response->getContent();
        $this->assertStringContainsString('bg-indigo-50/80', $html);
        $this->assertStringNotContainsString('Search permissions…', $html);
    }

    public function test_role_form_uses_same_permission_module_cards(): void
    {
        $this->actingAs($this->admin())
            ->get(route('admin.roles.create'))
            ->assertOk()
            ->assertSee('Total permissions')
            ->assertSee('Modules')
            ->assertSee('name="permissions[]"', false)
            ->assertSee('cms.create')
            ->assertSee('All');
    }

    public function test_role_update_syncs_selected_permissions(): void
    {
        $role = Role::create(['name' => 'ops', 'guard_name' => 'web']);

        $this->actingAs($this->admin())
            ->put(route('admin.roles.update', $role), [
                'name' => 'ops',
                'permissions' => ['cms.view', 'cms.edit'],
            ])
            ->assertRedirect(route('admin.roles.index'));

        $role->refresh();
        $this->assertTrue($role->hasPermissionTo('cms.view'));
        $this->assertTrue($role->hasPermissionTo('cms.edit'));
        $this->assertFalse($role->hasPermissionTo('cms.delete'));
    }
}
