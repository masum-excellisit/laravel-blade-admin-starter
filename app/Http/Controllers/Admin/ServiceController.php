<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\HandlesBulkActions;
use App\Http\Controllers\Admin\Concerns\HandlesListQuery;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ServiceRequest;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ServiceController extends Controller
{
    use HandlesBulkActions;
    use HandlesListQuery;

    public function index(Request $request)
    {
        abort_unless($request->user()->can('services.view'), 403);

        $services = $this->applyListQuery(
            Service::query(),
            $request,
            searchable: ['title', 'excerpt'],
            sortable: ['title', 'status', 'sort_order', 'created_at'],
            defaultSort: 'sort_order',
            defaultDirection: 'asc',
        )
            ->when($request->status, fn ($q, $status) => $q->where('status', $status))
            ->paginate(12)
            ->withQueryString();

        return view('admin.services.index', compact('services'));
    }

    public function create()
    {
        abort_unless(auth()->user()->can('services.create'), 403);

        return view('admin.services.create', [
            'service' => new Service(['status' => 'draft', 'sort_order' => 0]),
        ]);
    }

    public function store(ServiceRequest $request)
    {
        abort_unless($request->user()->can('services.create'), 403);

        $data = $request->validated();
        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('services', 'public');
        }
        Service::create($data);

        return redirect()->route('admin.services.index')->with('success', 'Service created.');
    }

    public function edit(Service $service)
    {
        abort_unless(auth()->user()->can('services.view'), 403);

        return view('admin.services.edit', compact('service'));
    }

    public function update(ServiceRequest $request, Service $service)
    {
        abort_unless($request->user()->can('services.edit'), 403);

        $data = $request->validated();
        if ($request->hasFile('image')) {
            if ($service->image) {
                Storage::disk('public')->delete($service->image);
            }
            $data['image'] = $request->file('image')->store('services', 'public');
        }
        $service->update($data);

        return redirect()->route('admin.services.index')->with('success', 'Service updated.');
    }

    public function destroy(Request $request, Service $service)
    {
        abort_unless($request->user()->can('services.delete'), 403);

        if ($service->image) {
            Storage::disk('public')->delete($service->image);
        }
        $service->delete();

        return back()->with('success', 'Service deleted.');
    }

    public function bulk(Request $request)
    {
        return $this->runBulkAction($request, Service::class, 'services');
    }
}
