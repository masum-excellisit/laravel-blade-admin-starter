<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\HandlesBulkActions;
use App\Http\Controllers\Admin\Concerns\HandlesListQuery;
use App\Http\Controllers\Controller;
use App\Models\Redirect;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RedirectController extends Controller
{
    use HandlesBulkActions;
    use HandlesListQuery;

    public function index(Request $request)
    {
        abort_unless($request->user()->can('redirects.view'), 403);

        $redirects = $this->applyListQuery(
            Redirect::query(),
            $request,
            searchable: ['from_path', 'to_url'],
            sortable: ['from_path', 'to_url', 'status_code', 'is_active', 'hits', 'created_at'],
            defaultSort: 'created_at',
            defaultDirection: 'desc',
        )
            ->when($request->status === 'active', fn ($query) => $query->where('is_active', true))
            ->when($request->status === 'inactive', fn ($query) => $query->where('is_active', false))
            ->paginate(12)
            ->withQueryString();

        return view('admin.redirects.index', compact('redirects'));
    }

    public function create()
    {
        abort_unless(auth()->user()->can('redirects.create'), 403);

        return view('admin.redirects.create', [
            'redirect' => new Redirect([
                'status_code' => 301,
                'is_active' => true,
                'hits' => 0,
            ]),
        ]);
    }

    public function store(Request $request)
    {
        abort_unless($request->user()->can('redirects.create'), 403);

        Redirect::create($this->validated($request));

        return redirect()->route('admin.redirects.index')->with('success', 'Redirect created.');
    }

    public function edit(Redirect $redirect)
    {
        abort_unless(auth()->user()->can('redirects.view'), 403);

        return view('admin.redirects.edit', compact('redirect'));
    }

    public function update(Request $request, Redirect $redirect)
    {
        abort_unless($request->user()->can('redirects.edit'), 403);

        $redirect->update($this->validated($request, $redirect));

        return redirect()->route('admin.redirects.index')->with('success', 'Redirect updated.');
    }

    public function destroy(Request $request, Redirect $redirect)
    {
        abort_unless($request->user()->can('redirects.delete'), 403);

        $redirect->delete();

        return back()->with('success', 'Redirect deleted.');
    }

    public function bulk(Request $request)
    {
        return $this->runBulkAction($request, Redirect::class, 'redirects', function ($query, string $action) use ($request): void {
            match ($action) {
                'delete' => tap($query)->get()->each(function (Redirect $redirect) use ($request): void {
                    abort_unless($request->user()->can('redirects.delete'), 403);
                    $redirect->delete();
                }),
                'activate' => $this->bulkSetStatus($request, $query, 'redirects', true, 'is_active'),
                'deactivate' => $this->bulkSetStatus($request, $query, 'redirects', false, 'is_active'),
                default => abort(422, 'Unknown bulk action.'),
            };
        });
    }

    private function validated(Request $request, ?Redirect $redirect = null): array
    {
        if ($request->filled('from_path')) {
            $request->merge(['from_path' => '/'.ltrim($request->input('from_path'), '/')]);
        }

        $data = $request->validate([
            'from_path' => [
                'required',
                'string',
                'max:255',
                Rule::unique('redirects', 'from_path')->ignore($redirect?->id),
            ],
            'to_url' => ['required', 'string', 'max:255'],
            'status_code' => ['required', 'integer', Rule::in([301, 302, 303, 307, 308])],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
