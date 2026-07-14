<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::with('parent')->withCount('posts')->latest()->get();
        $parents = Category::pluck('name', 'id');

        return view('admin.categories.index', compact('categories', 'parents'));
    }

    public function store(Request $request)
    {
        abort_unless($request->user()->can('categories.create'), 403);
        $data = $this->validated($request);
        Category::create($data);

        return back()->with('success', 'Category created.');
    }

    public function update(Request $request, Category $category)
    {
        abort_unless($request->user()->can('categories.edit'), 403);
        $data = $this->validated($request, $category->id);
        $category->update($data);

        return back()->with('success', 'Category updated.');
    }

    public function destroy(Request $request, Category $category)
    {
        abort_unless($request->user()->can('categories.delete'), 403);
        $category->delete();

        return back()->with('success', 'Category deleted.');
    }

    protected function validated(Request $request, $id = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', Rule::unique('categories', 'slug')->ignore($id)],
            'parent_id' => ['nullable', 'exists:categories,id'],
        ]);
        $data['slug'] = Str::slug($data['slug'] ?? $data['name']);

        return $data;
    }
}
