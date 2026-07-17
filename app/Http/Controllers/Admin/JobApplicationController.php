<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\HandlesBulkActions;
use App\Http\Controllers\Admin\Concerns\HandlesListQuery;
use App\Http\Controllers\Controller;
use App\Models\JobApplication;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class JobApplicationController extends Controller
{
    use HandlesBulkActions;
    use HandlesListQuery;

    public function index(Request $request)
    {
        abort_unless($request->user()->can('job-applications.view'), 403);

        $applications = $this->applyListQuery(
            JobApplication::query()->with('jobListing'),
            $request,
            searchable: ['name', 'email'],
            sortable: ['name', 'email', 'status', 'created_at'],
            defaultSort: 'created_at',
            defaultDirection: 'desc',
        )
            ->when($request->status, fn ($q, $status) => $q->where('status', $status))
            ->paginate(15)
            ->withQueryString();

        return view('admin.job-applications.index', compact('applications'));
    }

    public function show(JobApplication $jobApplication)
    {
        abort_unless(auth()->user()->can('job-applications.view'), 403);

        $jobApplication->load('jobListing');

        return view('admin.job-applications.show', ['application' => $jobApplication]);
    }

    public function update(Request $request, JobApplication $jobApplication)
    {
        abort_unless($request->user()->can('job-applications.edit'), 403);

        $data = $request->validate([
            'status' => ['required', Rule::in(['new', 'reviewed', 'shortlisted', 'rejected', 'hired'])],
        ]);

        $jobApplication->update($data);

        return back()->with('success', 'Application status updated.');
    }

    public function destroy(Request $request, JobApplication $jobApplication)
    {
        abort_unless($request->user()->can('job-applications.delete'), 403);

        if ($jobApplication->resume_path) {
            Storage::disk('public')->delete($jobApplication->resume_path);
        }
        $jobApplication->delete();

        return redirect()->route('admin.job-applications.index')->with('success', 'Application deleted.');
    }

    public function bulk(Request $request)
    {
        return $this->runBulkAction($request, JobApplication::class, 'job-applications', function ($query, $action, $ids) use ($request) {
            match ($action) {
                'delete' => tap($query)->get()->each(function (Model $model) use ($request) {
                    abort_unless($request->user()->can('job-applications.delete'), 403);
                    if ($model->resume_path) {
                        Storage::disk('public')->delete($model->resume_path);
                    }
                    $model->delete();
                }),
                'reviewed', 'shortlisted', 'rejected' => $this->bulkSetStatus($request, $query, 'job-applications', $action),
                default => abort(422, 'Unknown bulk action.'),
            };
        });
    }
}
