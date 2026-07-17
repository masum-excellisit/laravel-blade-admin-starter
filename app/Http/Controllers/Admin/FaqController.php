<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\HandlesBulkActions;
use App\Http\Controllers\Admin\Concerns\HandlesListQuery;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\FaqRequest;
use App\Models\Faq;
use Illuminate\Http\Request;

class FaqController extends Controller
{
    use HandlesBulkActions;
    use HandlesListQuery;

    public function index(Request $request)
    {
        abort_unless($request->user()->can('faqs.view'), 403);

        $faqs = $this->applyListQuery(
            Faq::query(),
            $request,
            searchable: ['question', 'answer', 'category'],
            sortable: ['question', 'category', 'sort_order', 'status', 'created_at'],
            defaultSort: 'sort_order',
            defaultDirection: 'asc',
        )
            ->when($request->status, fn ($q, $status) => $q->where('status', $status))
            ->paginate(12)
            ->withQueryString();

        return view('admin.faqs.index', compact('faqs'));
    }

    public function create()
    {
        abort_unless(auth()->user()->can('faqs.create'), 403);

        return view('admin.faqs.create', [
            'faq' => new Faq(['status' => 'draft', 'sort_order' => 0]),
        ]);
    }

    public function store(FaqRequest $request)
    {
        abort_unless($request->user()->can('faqs.create'), 403);

        Faq::create($request->validated());

        return redirect()->route('admin.faqs.index')->with('success', 'FAQ created.');
    }

    public function edit(Faq $faq)
    {
        abort_unless(auth()->user()->can('faqs.view'), 403);

        return view('admin.faqs.edit', compact('faq'));
    }

    public function update(FaqRequest $request, Faq $faq)
    {
        abort_unless($request->user()->can('faqs.edit'), 403);

        $faq->update($request->validated());

        return redirect()->route('admin.faqs.index')->with('success', 'FAQ updated.');
    }

    public function destroy(Request $request, Faq $faq)
    {
        abort_unless($request->user()->can('faqs.delete'), 403);

        $faq->delete();

        return back()->with('success', 'FAQ deleted.');
    }

    public function bulk(Request $request)
    {
        return $this->runBulkAction($request, Faq::class, 'faqs');
    }
}
