<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Post;

class BlogController extends Controller
{
    public function index()
    {
        $posts = Post::published()->with('category', 'author')->latest('published_at')->paginate(9);
        $categories = Category::withCount(['posts' => fn ($q) => $q->published()])->having('posts_count', '>', 0)->get();

        return view('site.blog.index', compact('posts', 'categories'));
    }

    public function category(string $slug)
    {
        $category = Category::where('slug', $slug)->firstOrFail();
        $posts = $category->posts()->published()->with('category', 'author')->latest('published_at')->paginate(9);
        $categories = Category::withCount(['posts' => fn ($q) => $q->published()])->having('posts_count', '>', 0)->get();

        return view('site.blog.index', compact('posts', 'categories', 'category'));
    }

    public function show(string $slug)
    {
        $post = Post::published()->with('category', 'author')->where('slug', $slug)->firstOrFail();
        $related = Post::published()->where('id', '!=', $post->id)
            ->when($post->category_id, fn ($q) => $q->where('category_id', $post->category_id))
            ->latest('published_at')->take(3)->get();

        return view('site.blog.show', compact('post', 'related'));
    }
}
