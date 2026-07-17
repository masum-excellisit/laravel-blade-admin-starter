<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\HandlesBulkActions;
use App\Http\Controllers\Admin\Concerns\HandlesListQuery;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\TestimonialRequest;
use App\Models\Testimonial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TestimonialController extends Controller
{
    use HandlesBulkActions;
    use HandlesListQuery;

    public function index(Request $request)
    {
        abort_unless($request->user()->can('testimonials.view'), 403);

        $testimonials = $this->applyListQuery(
            Testimonial::query(),
            $request,
            searchable: ['author_name', 'quote'],
            sortable: ['author_name', 'rating', 'sort_order', 'status', 'created_at'],
            defaultSort: 'sort_order',
            defaultDirection: 'asc',
        )
            ->when($request->status, fn ($q, $status) => $q->where('status', $status))
            ->paginate(12)
            ->withQueryString();

        return view('admin.testimonials.index', compact('testimonials'));
    }

    public function create()
    {
        abort_unless(auth()->user()->can('testimonials.create'), 403);

        return view('admin.testimonials.create', [
            'testimonial' => new Testimonial(['status' => 'draft', 'sort_order' => 0, 'rating' => 5]),
        ]);
    }

    public function store(TestimonialRequest $request)
    {
        abort_unless($request->user()->can('testimonials.create'), 403);

        $data = $request->validated();
        if ($request->hasFile('avatar')) {
            $data['avatar'] = $request->file('avatar')->store('testimonials', 'public');
        }
        Testimonial::create($data);

        return redirect()->route('admin.testimonials.index')->with('success', 'Testimonial created.');
    }

    public function edit(Testimonial $testimonial)
    {
        abort_unless(auth()->user()->can('testimonials.view'), 403);

        return view('admin.testimonials.edit', compact('testimonial'));
    }

    public function update(TestimonialRequest $request, Testimonial $testimonial)
    {
        abort_unless($request->user()->can('testimonials.edit'), 403);

        $data = $request->validated();
        if ($request->hasFile('avatar')) {
            if ($testimonial->avatar) {
                Storage::disk('public')->delete($testimonial->avatar);
            }
            $data['avatar'] = $request->file('avatar')->store('testimonials', 'public');
        }
        $testimonial->update($data);

        return redirect()->route('admin.testimonials.index')->with('success', 'Testimonial updated.');
    }

    public function destroy(Request $request, Testimonial $testimonial)
    {
        abort_unless($request->user()->can('testimonials.delete'), 403);

        if ($testimonial->avatar) {
            Storage::disk('public')->delete($testimonial->avatar);
        }
        $testimonial->delete();

        return back()->with('success', 'Testimonial deleted.');
    }

    public function bulk(Request $request)
    {
        return $this->runBulkAction($request, Testimonial::class, 'testimonials');
    }
}
