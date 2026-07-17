<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\Service;
use App\Models\Testimonial;

class HomeController extends Controller
{
    public function index()
    {
        $posts = Post::published()->with('category')->latest('published_at')->take(3)->get();
        $services = Service::published()->orderBy('sort_order')->take(3)->get();
        $testimonials = Testimonial::published()->orderBy('sort_order')->take(3)->get();

        return view('site.home', compact('posts', 'services', 'testimonials'));
    }
}
