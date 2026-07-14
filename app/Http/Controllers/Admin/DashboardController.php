<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use App\Models\Page;
use App\Models\Post;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            ['label' => 'Users', 'value' => User::count(), 'route' => 'admin.users.index', 'can' => 'users.view'],
            ['label' => 'Pages', 'value' => Page::count(), 'route' => 'admin.pages.index', 'can' => 'pages.view'],
            ['label' => 'Posts', 'value' => Post::count(), 'route' => 'admin.posts.index', 'can' => 'posts.view'],
            ['label' => 'Messages', 'value' => ContactMessage::where('read', false)->count(), 'route' => 'admin.messages.index', 'can' => 'messages.view'],
        ];

        $recentPosts = Post::latest()->take(5)->get();
        $recentUsers = User::latest()->take(5)->get();

        return view('admin.dashboard', compact('stats', 'recentPosts', 'recentUsers'));
    }
}
