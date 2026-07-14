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
            ['email' => 'admin@example.com'],
            ['name' => 'Super Admin', 'password' => Hash::make('password'), 'status' => true, 'email_verified_at' => now()]
        );
        $super->syncRoles('super-admin');

        $editor = User::updateOrCreate(
            ['email' => 'editor@example.com'],
            ['name' => 'Ellie Editor', 'password' => Hash::make('password'), 'status' => true, 'email_verified_at' => now()]
        );
        $editor->syncRoles('editor');
    }
}
