<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Post;

class HomeController extends Controller
{
    public function index()
    {
        $posts = Post::published()->with('category')->latest('published_at')->take(3)->get();

        return view('site.home', compact('posts'));
    }
}
