<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\HandlesBulkActions;
use App\Http\Controllers\Admin\Concerns\HandlesListQuery;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PostRequest;
use App\Models\Category;
use App\Models\Post;
use App\Models\Revision;
use App\Support\Activity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PostController extends Controller
{
    use HandlesBulkActions, HandlesListQuery;

    public function index(Request $request)
    {
        $query = Post::with('category', 'author');

        if ($request->status) {
            $query->where('status', $request->status);
        }

        $posts = $this->applyListQuery(
            $query,
            $request,
            ['title'],
            ['title', 'status', 'published_at', 'created_at'],
        )->paginate(12)->withQueryString();

        return view('admin.posts.index', compact('posts'));
    }

    public function bulk(Request $request)
    {
        return $this->runBulkAction($request, Post::class, 'posts');
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
        $post = Post::create($data);

        Activity::log('created', $post, 'Post created');

        return redirect()->route('admin.posts.index')->with('success', 'Post created.');
    }

    public function edit(Post $post)
    {
        return view('admin.posts.edit', [
            'post' => $post,
            'categories' => Category::pluck('name', 'id'),
            'revisions' => $post->revisions()->with('user')->latest()->limit(5)->get(),
        ]);
    }

    public function update(PostRequest $request, Post $post)
    {
        abort_unless($request->user()->can('posts.edit'), 403);
        $data = $request->validated();
        $oldImage = $post->featured_image;
        if ($request->hasFile('featured_image')) {
            $data['featured_image'] = $request->file('featured_image')->store('posts', 'public');
        }
        $post->fill($data);

        if ($post->isDirty()) {
            $post->recordRevision('before update');
            $post->save();
            if (($data['featured_image'] ?? null) && $oldImage) {
                Storage::disk('public')->delete($oldImage);
            }
            Activity::log('updated', $post, 'Post updated');
        }

        return redirect()->route('admin.posts.index')->with('success', 'Post updated.');
    }

    public function destroy(Request $request, Post $post)
    {
        abort_unless($request->user()->can('posts.delete'), 403);
        Activity::log('deleted', $post, 'Post deleted', ['title' => $post->title]);
        $post->delete();

        return back()->with('success', 'Post deleted.');
    }

    public function restoreRevision(Request $request, Post $post, Revision $revision)
    {
        abort_unless($request->user()->can('posts.edit'), 403);
        abort_unless($revision->revisionable_type === $post->getMorphClass()
            && (int) $revision->revisionable_id === (int) $post->id, 404);

        $post->restoreRevision($revision);
        Activity::log('restored', $post, 'Post revision restored', [
            'revision_id' => $revision->id,
        ]);

        return redirect()->route('admin.posts.edit', $post)->with('success', 'Post revision restored.');
    }
}
