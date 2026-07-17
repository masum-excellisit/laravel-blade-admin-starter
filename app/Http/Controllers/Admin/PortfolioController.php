<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\HandlesBulkActions;
use App\Http\Controllers\Admin\Concerns\HandlesListQuery;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PortfolioItemRequest;
use App\Models\PortfolioItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PortfolioController extends Controller
{
    use HandlesBulkActions;
    use HandlesListQuery;

    public function index(Request $request)
    {
        abort_unless($request->user()->can('portfolio.view'), 403);

        $portfolioItems = $this->applyListQuery(
            PortfolioItem::query(),
            $request,
            searchable: ['title', 'client', 'excerpt'],
            sortable: ['title', 'client', 'sort_order', 'status', 'published_at', 'created_at'],
            defaultSort: 'sort_order',
            defaultDirection: 'asc',
        )
            ->when($request->status, fn ($q, $status) => $q->where('status', $status))
            ->paginate(12)
            ->withQueryString();

        return view('admin.portfolio.index', compact('portfolioItems'));
    }

    public function create()
    {
        abort_unless(auth()->user()->can('portfolio.create'), 403);

        return view('admin.portfolio.create', [
            'portfolioItem' => new PortfolioItem(['status' => 'draft', 'sort_order' => 0]),
        ]);
    }

    public function store(PortfolioItemRequest $request)
    {
        abort_unless($request->user()->can('portfolio.create'), 403);

        $data = $request->validated();
        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('portfolio', 'public');
        }
        PortfolioItem::create($data);

        return redirect()->route('admin.portfolio.index')->with('success', 'Portfolio item created.');
    }

    public function edit(PortfolioItem $portfolioItem)
    {
        abort_unless(auth()->user()->can('portfolio.view'), 403);

        return view('admin.portfolio.edit', compact('portfolioItem'));
    }

    public function update(PortfolioItemRequest $request, PortfolioItem $portfolioItem)
    {
        abort_unless($request->user()->can('portfolio.edit'), 403);

        $data = $request->validated();
        if ($request->hasFile('image')) {
            if ($portfolioItem->image) {
                Storage::disk('public')->delete($portfolioItem->image);
            }
            $data['image'] = $request->file('image')->store('portfolio', 'public');
        }
        $portfolioItem->update($data);

        return redirect()->route('admin.portfolio.index')->with('success', 'Portfolio item updated.');
    }

    public function destroy(Request $request, PortfolioItem $portfolioItem)
    {
        abort_unless($request->user()->can('portfolio.delete'), 403);

        if ($portfolioItem->image) {
            Storage::disk('public')->delete($portfolioItem->image);
        }
        $portfolioItem->delete();

        return back()->with('success', 'Portfolio item deleted.');
    }

    public function bulk(Request $request)
    {
        return $this->runBulkAction($request, PortfolioItem::class, 'portfolio');
    }
}
