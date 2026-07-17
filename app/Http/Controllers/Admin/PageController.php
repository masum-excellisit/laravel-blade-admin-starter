<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\HandlesBulkActions;
use App\Http\Controllers\Admin\Concerns\HandlesListQuery;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PageRequest;
use App\Models\Page;
use Illuminate\Http\Request;

class PageController extends Controller
{
    use HandlesBulkActions, HandlesListQuery;

    protected array $templates = ['default' => 'Default', 'full-width' => 'Full width', 'sidebar' => 'With sidebar'];

    public function index(Request $request)
    {
        $query = Page::query();

        if ($request->status) {
            $query->where('status', $request->status);
        }

        $pages = $this->applyListQuery(
            $query,
            $request,
            ['title', 'slug'],
            ['title', 'slug', 'template', 'status', 'created_at'],
        )->paginate(12)->withQueryString();

        return view('admin.pages.index', compact('pages'));
    }

    public function bulk(Request $request)
    {
        return $this->runBulkAction($request, Page::class, 'pages');
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
