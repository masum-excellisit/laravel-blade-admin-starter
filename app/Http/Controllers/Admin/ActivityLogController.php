<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\HandlesBulkActions;
use App\Http\Controllers\Admin\Concerns\HandlesListQuery;
use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    use HandlesBulkActions, HandlesListQuery;

    public function index(Request $request)
    {
        $logs = $this->applyListQuery(
            ActivityLog::query()->with('user'),
            $request,
            ['action', 'description', 'subject_type', 'ip_address', 'user.name', 'user.email'],
            ['action', 'subject_type', 'ip_address', 'created_at'],
        )->paginate(20)->withQueryString();

        return view('admin.activity-logs.index', compact('logs'));
    }

    public function destroy(Request $request, ActivityLog $activityLog)
    {
        abort_unless($request->user()->can('activity-logs.delete'), 403);

        $activityLog->delete();

        return back()->with('success', 'Activity log deleted.');
    }

    public function bulk(Request $request)
    {
        return $this->runBulkAction($request, ActivityLog::class, 'activity-logs');
    }
}
