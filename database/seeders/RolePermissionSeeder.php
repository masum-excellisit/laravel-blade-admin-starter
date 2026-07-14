<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $modules = ['users', 'roles', 'permissions', 'pages', 'posts', 'categories', 'menus', 'media', 'settings', 'messages'];
        $actions = ['view', 'create', 'edit', 'delete'];

        foreach ($modules as $module) {
            foreach ($actions as $action) {
                Permission::firstOrCreate(['name' => "{$module}.{$action}", 'guard_name' => 'web']);
            }
        }

        $superAdmin = Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'web']);
        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $editor = Role::firstOrCreate(['name' => 'editor', 'guard_name' => 'web']);

        // super-admin bypasses via Gate::before, but sync all for clarity
        $superAdmin->syncPermissions(Permission::all());

        $admin->syncPermissions(Permission::whereNotIn('name', [
            'permissions.create', 'permissions.edit', 'permissions.delete',
            'settings.delete',
        ])->get());

        $editor->syncPermissions(Permission::whereIn('name', [
            'pages.view', 'pages.create', 'pages.edit',
            'posts.view', 'posts.create', 'posts.edit', 'posts.delete',
            'categories.view', 'categories.create', 'categories.edit',
            'media.view', 'media.create', 'media.delete',
            'menus.view', 'menus.edit',
            'messages.view',
        ])->get());
    }
}
