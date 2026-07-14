<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $super = User::updateOrCreate(
            ['email' => 'superadmin@yopmail.com'],
            ['name' => 'Super Admin', 'password' => Hash::make('12345678'), 'status' => true, 'email_verified_at' => now()]
        );
        $super->syncRoles('super-admin');

        $admin = User::updateOrCreate(
            ['email' => 'main@yopmail.com'],
            ['name' => 'Admin', 'password' => Hash::make('12345678'), 'status' => true, 'email_verified_at' => now()]
        );
        $admin->syncRoles('admin');

        $editor = User::updateOrCreate(
            ['email' => 'editor@yopmail.com'],
            ['name' => 'Ellie Editor', 'password' => Hash::make('12345678'), 'status' => true, 'email_verified_at' => now()]
        );
        $editor->syncRoles('editor');
    }
}
