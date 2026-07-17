<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\HandlesBulkActions;
use App\Http\Controllers\Admin\Concerns\HandlesListQuery;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ContentBlockRequest;
use App\Models\ContentBlock;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class ContentBlockController extends Controller
{
    use HandlesBulkActions;
    use HandlesListQuery;

    public function index(Request $request)
    {
        abort_unless($request->user()->can('blocks.view'), 403);

        $blocks = $this->applyListQuery(
            ContentBlock::query(),
            $request,
            searchable: ['name', 'key', 'content'],
            sortable: ['name', 'key', 'type', 'is_active', 'created_at'],
            defaultSort: 'name',
            defaultDirection: 'asc',
        )
            ->when($request->filled('active'), fn (Builder $query) => $query->where('is_active', $request->boolean('active')))
            ->paginate(12)
            ->withQueryString();

        return view('admin.blocks.index', compact('blocks'));
    }

    public function create()
    {
        abort_unless(auth()->user()->can('blocks.create'), 403);

        return view('admin.blocks.create', [
            'block' => new ContentBlock(['type' => 'html', 'is_active' => true]),
        ]);
    }

    public function store(ContentBlockRequest $request)
    {
        abort_unless($request->user()->can('blocks.create'), 403);

        ContentBlock::create($request->validated());

        return redirect()->route('admin.blocks.index')->with('success', 'Content block created.');
    }

    public function edit(ContentBlock $block)
    {
        abort_unless(auth()->user()->can('blocks.view'), 403);

        return view('admin.blocks.edit', compact('block'));
    }

    public function update(ContentBlockRequest $request, ContentBlock $block)
    {
        abort_unless($request->user()->can('blocks.edit'), 403);

        $block->update($request->validated());

        return redirect()->route('admin.blocks.index')->with('success', 'Content block updated.');
    }

    public function destroy(Request $request, ContentBlock $block)
    {
        abort_unless($request->user()->can('blocks.delete'), 403);

        $block->delete();

        return back()->with('success', 'Content block deleted.');
    }

    public function bulk(Request $request)
    {
        return $this->runBulkAction(
            $request,
            ContentBlock::class,
            'blocks',
            function (Builder $query, string $action) use ($request) {
                match ($action) {
                    'delete' => tap($query)->get()->each(function (ContentBlock $block) use ($request) {
                        abort_unless($request->user()->can('blocks.delete'), 403);
                        $block->delete();
                    }),
                    'activate' => $this->bulkSetStatus($request, $query, 'blocks', true, 'is_active'),
                    'deactivate' => $this->bulkSetStatus($request, $query, 'blocks', false, 'is_active'),
                    default => abort(422, 'Unknown bulk action.'),
                };
            },
        );
    }
}
