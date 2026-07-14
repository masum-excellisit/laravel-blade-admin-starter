<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Page;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DemoContentSeeder extends Seeder
{
    public function run(): void
    {
        $author = User::admins()->first();

        // Demo customers (non-admin users)
        foreach ([
            ['Alice Nguyen', 'alice@example.com'],
            ['Bruno Silva', 'bruno@example.com'],
            ['Chen Wei', 'chen@example.com'],
        ] as [$name, $email]) {
            User::firstOrCreate(['email' => $email], [
                'name' => $name,
                'type' => User::TYPE_CUSTOMER,
                'password' => Hash::make('password'),
                'status' => true,
                'email_verified_at' => now(),
            ]);
        }

        $pages = [
            ['title' => 'About Us', 'is_static' => false, 'body' => '<h2>Who we are</h2><p>We build delightful software. This page is fully editable from the admin panel using the Jodit editor.</p>'],
            ['title' => 'Services', 'is_static' => false, 'body' => '<h2>What we do</h2><p>Design, development, and everything in between.</p>'],
            ['title' => 'Privacy Policy', 'is_static' => false, 'body' => '<p>Your privacy matters. Edit this content in the admin.</p>'],
        ];
        foreach ($pages as $p) {
            Page::firstOrCreate(['slug' => Str::slug($p['title'])], array_merge($p, [
                'status' => 'published',
                'meta_title' => $p['title'],
                'meta_description' => Str::limit(strip_tags($p['body']), 150),
            ]));
        }

        $categories = ['Announcements', 'Engineering', 'Design'];
        $catModels = [];
        foreach ($categories as $c) {
            $catModels[] = Category::firstOrCreate(['slug' => Str::slug($c)], ['name' => $c]);
        }

        $posts = [
            'Introducing the Blade Admin Starter',
            'How we built a themeable admin panel',
            'Designing for delight with Tailwind',
            'A guide to dynamic menus',
            'Shipping fast without breaking things',
        ];
        foreach ($posts as $i => $title) {
            Post::firstOrCreate(['slug' => Str::slug($title)], [
                'title' => $title,
                'excerpt' => 'A short teaser for "'.$title.'". Replace this with your own copy.',
                'body' => '<p>This is a demo post body rendered from the database. Edit it in the admin panel.</p><p>'.str_repeat('Lorem ipsum dolor sit amet, consectetur adipiscing elit. ', 8).'</p>',
                'category_id' => $catModels[$i % count($catModels)]->id,
                'author_id' => $author?->id,
                'status' => 'published',
                'published_at' => now()->subDays($i),
            ]);
        }

        // Menus
        $header = Menu::firstOrCreate(['location' => 'header'], ['name' => 'Header Menu']);
        if ($header->items()->count() === 0) {
            $order = 0;
            MenuItem::create(['menu_id' => $header->id, 'label' => 'Home', 'type' => 'route', 'value' => 'home', 'order' => $order++]);
            MenuItem::create(['menu_id' => $header->id, 'label' => 'Blog', 'type' => 'route', 'value' => 'blog.index', 'order' => $order++]);
            MenuItem::create(['menu_id' => $header->id, 'label' => 'About', 'type' => 'page', 'value' => 'about-us', 'order' => $order++]);
            MenuItem::create(['menu_id' => $header->id, 'label' => 'Services', 'type' => 'page', 'value' => 'services', 'order' => $order++]);
            MenuItem::create(['menu_id' => $header->id, 'label' => 'Contact', 'type' => 'route', 'value' => 'contact', 'order' => $order++]);
        }

        $footer = Menu::firstOrCreate(['location' => 'footer'], ['name' => 'Footer Menu']);
        if ($footer->items()->count() === 0) {
            $order = 0;
            MenuItem::create(['menu_id' => $footer->id, 'label' => 'Privacy Policy', 'type' => 'page', 'value' => 'privacy-policy', 'order' => $order++]);
            MenuItem::create(['menu_id' => $footer->id, 'label' => 'Blog', 'type' => 'route', 'value' => 'blog.index', 'order' => $order++]);
        }
    }
}
