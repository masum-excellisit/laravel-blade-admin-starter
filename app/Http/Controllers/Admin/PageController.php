<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PageRequest;
use App\Models\Page;
use Illuminate\Http\Request;

class PageController extends Controller
{
    protected array $templates = ['default' => 'Default', 'full-width' => 'Full width', 'sidebar' => 'With sidebar'];

    public function index(Request $request)
    {
        $pages = Page::when($request->search, fn ($q, $s) => $q->where('title', 'like', "%$s%"))
            ->latest()->paginate(12)->withQueryString();

        return view('admin.pages.index', compact('pages'));
    }

    public function create()
    {
        return view('admin.pages.create', ['page' => new Page(['status' => 'draft', 'template' => 'default']), 'templates' => $this->templates]);
    }

    public function store(PageRequest $request)
    {
        abort_unless($request->user()->can('pages.create'), 403);
        Page::create($request->validated());

        return redirect()->route('admin.pages.index')->with('success', 'Page created.');
    }

    public function edit(Page $page)
    {
        return view('admin.pages.edit', ['page' => $page, 'templates' => $this->templates]);
    }

    public function update(PageRequest $request, Page $page)
    {
        abort_unless($request->user()->can('pages.edit'), 403);
        $page->update($request->validated());

        return redirect()->route('admin.pages.index')->with('success', 'Page updated.');
    }

    public function destroy(Request $request, Page $page)
    {
        abort_unless($request->user()->can('pages.delete'), 403);
        $page->delete();

        return back()->with('success', 'Page deleted.');
    }
}
