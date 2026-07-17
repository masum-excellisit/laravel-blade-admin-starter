<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\CmsContent;
use App\Models\JobListing;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Page;
use App\Models\Post;
use App\Models\Service;
use App\Models\Testimonial;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DemoContentSeeder extends Seeder
{
    public function run(): void
    {
        $author = User::admins()->first();

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
            ['title' => 'Privacy Policy', 'is_static' => false, 'body' => '<p>Your privacy matters. Edit this content in the admin.</p>'],
        ];
        foreach ($pages as $p) {
            Page::firstOrCreate(['slug' => Str::slug($p['title'])], array_merge($p, [
                'status' => 'published',
                'meta_title' => $p['title'],
                'meta_description' => Str::limit(strip_tags($p['body']), 150),
            ]));
        }

        // CMS fixed-page defaults
        $cmsDefaults = [
            'home' => [
                'hero' => [
                    'eyebrow' => 'Built with Laravel + Tailwind',
                    'headline' => 'Launch faster with a premium admin starter',
                    'subheadline' => 'A themeable public site and custom Blade admin — pages, modules, menus, and roles ready out of the box.',
                    'cta_primary_text' => 'Read the blog',
                    'cta_primary_url' => '/blog',
                    'cta_secondary_text' => 'Get in touch',
                    'cta_secondary_url' => '/contact',
                ],
                'features' => [
                    'title' => 'Everything you need to launch',
                    'subtitle' => 'A custom admin panel, dynamic content, roles & permissions, and a themeable public site.',
                    'item_1_title' => 'Custom admin',
                    'item_1_body' => 'A premium, gradient, mobile-first Blade admin panel with dashboard, users, and settings.',
                    'item_2_title' => 'Dynamic content',
                    'item_2_body' => 'CMS sections, services, testimonials, jobs and drag-friendly menus — all editable.',
                    'item_3_title' => 'Fully themeable',
                    'item_3_body' => 'Change the gradient in Settings and both the admin and public site restyle instantly.',
                ],
            ],
            'about' => [
                'intro' => [
                    'title' => 'About us',
                    'body' => '<p>We help teams ship polished Laravel sites with a reusable admin starter.</p>',
                ],
                'mission' => [
                    'title' => 'Our mission',
                    'body' => 'Make premium admin tooling accessible for every client project.',
                ],
            ],
            'services' => [
                'intro' => [
                    'title' => 'Our services',
                    'subtitle' => 'Capabilities we bring to every engagement.',
                ],
            ],
            'how-it-works' => [
                'intro' => [
                    'title' => 'How it works',
                    'subtitle' => 'A simple path from idea to launch.',
                ],
                'steps' => [
                    'step_1_title' => 'Discover',
                    'step_1_body' => 'We learn your goals and map the content structure.',
                    'step_2_title' => 'Build',
                    'step_2_body' => 'Configure the admin modules and public pages.',
                    'step_3_title' => 'Launch',
                    'step_3_body' => 'Train your team and go live with confidence.',
                ],
            ],
            'careers' => [
                'intro' => [
                    'title' => 'Careers',
                    'subtitle' => 'Join a team that cares about craft.',
                    'body' => '<p>Browse open roles below and apply in a few minutes.</p>',
                ],
            ],
            'contact' => [
                'intro' => [
                    'title' => 'Contact us',
                    'subtitle' => 'We usually reply within one business day.',
                    'address' => "123 Starter Lane\nSuite 100",
                    'email' => 'hello@example.com',
                    'phone' => '+1 (555) 010-2000',
                ],
            ],
        ];

        foreach ($cmsDefaults as $page => $sections) {
            foreach ($sections as $section => $data) {
                CmsContent::updateOrCreate(
                    ['page' => $page, 'section' => $section],
                    ['data' => $data]
                );
            }
        }

        $serviceSeeds = [
            ['Web Design', 'Beautiful, responsive marketing sites.'],
            ['Laravel Development', 'Robust backends and custom admin panels.'],
            ['Content Strategy', 'Structured CMS that your team can own.'],
        ];
        foreach ($serviceSeeds as $i => [$title, $excerpt]) {
            Service::firstOrCreate(['slug' => Str::slug($title)], [
                'title' => $title,
                'excerpt' => $excerpt,
                'body' => '<p>'.$excerpt.' Edit this service from the admin panel.</p>',
                'sort_order' => $i,
                'status' => 'published',
            ]);
        }

        foreach ([
            ['Maya Patel', 'CEO, Northwind', 'This starter cut our launch timeline in half.'],
            ['Jon Reyes', 'Product Lead', 'The admin UX is clean and our editors love it.'],
            ['Sam Okonkwo', 'Founder', 'Roles, menus, and CMS sections just work.'],
        ] as $i => [$name, $title, $quote]) {
            Testimonial::firstOrCreate(
                ['author_name' => $name, 'quote' => $quote],
                [
                    'author_title' => $title,
                    'rating' => 5,
                    'sort_order' => $i,
                    'status' => 'published',
                ]
            );
        }

        JobListing::firstOrCreate(['slug' => 'senior-laravel-developer'], [
            'title' => 'Senior Laravel Developer',
            'location' => 'Remote',
            'employment_type' => 'full-time',
            'description' => '<p>Build and extend client sites on our Blade admin starter.</p>',
            'requirements' => '<ul><li>Laravel 10+</li><li>Blade &amp; Tailwind</li><li>Strong communication</li></ul>',
            'status' => 'published',
            'published_at' => now()->subDays(2),
        ]);

        JobListing::firstOrCreate(['slug' => 'content-editor'], [
            'title' => 'Content Editor',
            'location' => 'Hybrid',
            'employment_type' => 'part-time',
            'description' => '<p>Own CMS sections, blog posts, and service copy.</p>',
            'requirements' => '<ul><li>Strong writing</li><li>CMS experience</li></ul>',
            'status' => 'published',
            'published_at' => now()->subDay(),
        ]);

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

        $header = Menu::firstOrCreate(['location' => 'header'], ['name' => 'Header']);
        if ($header->items()->count() === 0) {
            $order = 0;
            foreach ([
                ['Home', 'route', 'home'],
                ['Services', 'route', 'services.index'],
                ['How it works', 'route', 'how-it-works'],
                ['Careers', 'route', 'careers'],
                ['Blog', 'route', 'blog.index'],
                ['About', 'route', 'about'],
                ['Contact', 'route', 'contact'],
            ] as [$label, $type, $value]) {
                MenuItem::create([
                    'menu_id' => $header->id,
                    'label' => $label,
                    'type' => $type,
                    'value' => $value,
                    'order' => $order++,
                ]);
            }
        }

        $footer = Menu::firstOrCreate(['location' => 'footer'], ['name' => 'Footer']);
        if ($footer->items()->count() === 0) {
            $order = 0;
            foreach ([
                ['Privacy Policy', 'page', 'privacy-policy'],
                ['Services', 'route', 'services.index'],
                ['Careers', 'route', 'careers'],
                ['Blog', 'route', 'blog.index'],
                ['Contact', 'route', 'contact'],
                ['Testimonials', 'route', 'home'],
                ['About', 'route', 'about'],
            ] as [$label, $type, $value]) {
                MenuItem::create([
                    'menu_id' => $footer->id,
                    'label' => $label,
                    'type' => $type,
                    'value' => $value,
                    'order' => $order++,
                ]);
            }
        }
    }
}
