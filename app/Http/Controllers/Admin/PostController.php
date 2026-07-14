<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PostRequest;
use App\Models\Category;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PostController extends Controller
{
    public function index(Request $request)
    {
        $posts = Post::with('category', 'author')
            ->when($request->search, fn ($q, $s) => $q->where('title', 'like', "%$s%"))
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->latest()->paginate(12)->withQueryString();

        return view('admin.posts.index', compact('posts'));
    }

    public function create()
    {
        return view('admin.posts.create', ['post' => new Post(['status' => 'draft']), 'categories' => Category::pluck('name', 'id')]);
    }

    public function store(PostRequest $request)
    {
        abort_unless($request->user()->can('posts.create'), 403);
        $data = $request->validated();
        $data['author_id'] = $request->user()->id;
        if ($request->hasFile('featured_image')) {
            $data['featured_image'] = $request->file('featured_image')->store('posts', 'public');
        }
        Post::create($data);

        return redirect()->route('admin.posts.index')->with('success', 'Post created.');
    }

    public function edit(Post $post)
    {
        return view('admin.posts.edit', ['post' => $post, 'categories' => Category::pluck('name', 'id')]);
    }

    public function update(PostRequest $request, Post $post)
    {
        abort_unless($request->user()->can('posts.edit'), 403);
        $data = $request->validated();
        if ($request->hasFile('featured_image')) {
            if ($post->featured_image) {
                Storage::disk('public')->delete($post->featured_image);
            }
            $data['featured_image'] = $request->file('featured_image')->store('posts', 'public');
        }
        $post->update($data);

        return redirect()->route('admin.posts.index')->with('success', 'Post updated.');
    }

    public function destroy(Request $request, Post $post)
    {
        abort_unless($request->user()->can('posts.delete'), 403);
        $post->delete();

        return back()->with('success', 'Post deleted.');
    }
}
