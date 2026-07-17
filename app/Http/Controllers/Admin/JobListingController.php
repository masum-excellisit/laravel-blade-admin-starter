<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\HandlesBulkActions;
use App\Http\Controllers\Admin\Concerns\HandlesListQuery;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\JobListingRequest;
use App\Models\JobListing;
use Illuminate\Http\Request;

class JobListingController extends Controller
{
    use HandlesBulkActions;
    use HandlesListQuery;

    public function index(Request $request)
    {
        abort_unless($request->user()->can('jobs.view'), 403);

        $jobs = $this->applyListQuery(
            JobListing::query(),
            $request,
            searchable: ['title', 'location'],
            sortable: ['title', 'location', 'employment_type', 'status', 'published_at', 'created_at'],
            defaultSort: 'created_at',
            defaultDirection: 'desc',
        )
            ->when($request->status, fn ($q, $status) => $q->where('status', $status))
            ->paginate(12)
            ->withQueryString();

        return view('admin.jobs.index', compact('jobs'));
    }

    public function create()
    {
        abort_unless(auth()->user()->can('jobs.create'), 403);

        return view('admin.jobs.create', [
            'jobListing' => new JobListing(['status' => 'draft', 'employment_type' => 'full-time']),
        ]);
    }

    public function store(JobListingRequest $request)
    {
        abort_unless($request->user()->can('jobs.create'), 403);

        JobListing::create($request->validated());

        return redirect()->route('admin.jobs.index')->with('success', 'Job listing created.');
    }

    public function edit(JobListing $jobListing)
    {
        abort_unless(auth()->user()->can('jobs.view'), 403);

        return view('admin.jobs.edit', compact('jobListing'));
    }

    public function update(JobListingRequest $request, JobListing $jobListing)
    {
        abort_unless($request->user()->can('jobs.edit'), 403);

        $jobListing->update($request->validated());

        return redirect()->route('admin.jobs.index')->with('success', 'Job listing updated.');
    }

    public function destroy(Request $request, JobListing $jobListing)
    {
        abort_unless($request->user()->can('jobs.delete'), 403);

        $jobListing->delete();

        return back()->with('success', 'Job listing deleted.');
    }

    public function bulk(Request $request)
    {
        return $this->runBulkAction($request, JobListing::class, 'jobs');
    }
}
