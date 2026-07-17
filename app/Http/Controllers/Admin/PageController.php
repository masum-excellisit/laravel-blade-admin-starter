<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\HandlesBulkActions;
use App\Http\Controllers\Admin\Concerns\HandlesListQuery;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PageRequest;
use App\Models\Page;
use App\Models\Revision;
use App\Support\Activity;
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
        $page = Page::create($request->validated());

        Activity::log('created', $page, 'Page created');

        return redirect()->route('admin.pages.index')->with('success', 'Page created.');
    }

    public function edit(Page $page)
    {
        return view('admin.pages.edit', [
            'page' => $page,
            'templates' => $this->templates,
            'revisions' => $page->revisions()->with('user')->latest()->limit(5)->get(),
        ]);
    }

    public function update(PageRequest $request, Page $page)
    {
        abort_unless($request->user()->can('pages.edit'), 403);
        $page->fill($request->validated());

        if ($page->isDirty()) {
            $page->recordRevision('before update');
            $page->save();
            Activity::log('updated', $page, 'Page updated');
        }

        return redirect()->route('admin.pages.index')->with('success', 'Page updated.');
    }

    public function destroy(Request $request, Page $page)
    {
        abort_unless($request->user()->can('pages.delete'), 403);
        Activity::log('deleted', $page, 'Page deleted', ['title' => $page->title]);
        $page->delete();

        return back()->with('success', 'Page deleted.');
    }

    public function restoreRevision(Request $request, Page $page, Revision $revision)
    {
        abort_unless($request->user()->can('pages.edit'), 403);
        abort_unless($revision->revisionable_type === $page->getMorphClass()
            && (int) $revision->revisionable_id === (int) $page->id, 404);

        $page->restoreRevision($revision);
        Activity::log('restored', $page, 'Page revision restored', [
            'revision_id' => $revision->id,
        ]);

        return redirect()->route('admin.pages.edit', $page)->with('success', 'Page revision restored.');
    }
}
