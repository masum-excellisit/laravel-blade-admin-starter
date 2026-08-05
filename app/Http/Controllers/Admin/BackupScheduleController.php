<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BackupSchedule;
use App\Services\BackupManager;
use App\Support\Activity;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BackupScheduleController extends Controller
{
    public function __construct(private readonly BackupManager $manager) {}

    public function store(Request $request)
    {
        abort_unless($request->user()->can('backups.edit'), 403);

        $schedule = BackupSchedule::create($this->validated($request));

        Activity::log('created', $schedule, "Added backup schedule {$schedule->name}");

        return back()->with('success', "Schedule “{$schedule->name}” added — {$schedule->summary}.");
    }

    public function update(Request $request, BackupSchedule $schedule)
    {
        abort_unless($request->user()->can('backups.edit'), 403);

        $schedule->update($this->validated($request));

        Activity::log('updated', $schedule, "Updated backup schedule {$schedule->name}");

        return back()->with('success', "Schedule “{$schedule->name}” updated.");
    }

    public function toggle(Request $request, BackupSchedule $schedule)
    {
        abort_unless($request->user()->can('backups.edit'), 403);

        $schedule->update(['is_active' => ! $schedule->is_active]);

        return back()->with('success', $schedule->is_active
            ? "Schedule “{$schedule->name}” resumed."
            : "Schedule “{$schedule->name}” paused.");
    }

    public function run(Request $request, BackupSchedule $schedule)
    {
        abort_unless($request->user()->can('backups.create'), 403);

        $backup = $this->manager->create($schedule->type, 'scheduled', $request->user()->id, $schedule->id);
        $schedule->update(['last_run_at' => now()]);

        if ($backup->status === 'failed') {
            return back()->with('error', 'Backup failed: '.$backup->error);
        }

        $this->manager->prune($schedule->retention, $schedule->id);

        Activity::log('created', $backup, "Ran schedule {$schedule->name} manually");

        return back()->with('success', "Schedule “{$schedule->name}” ran now — {$backup->name} ({$backup->size_for_humans}).");
    }

    public function destroy(Request $request, BackupSchedule $schedule)
    {
        abort_unless($request->user()->can('backups.delete'), 403);

        $name = $schedule->name;
        $schedule->delete();

        Activity::log('deleted', null, "Deleted backup schedule {$name}");

        return back()->with('success', "Schedule “{$name}” deleted. Existing backups were kept.");
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'type' => ['required', Rule::in(array_keys(BackupManager::TYPES))],
            'frequency' => ['required', Rule::in(array_keys(BackupSchedule::FREQUENCIES))],
            'time' => ['required', 'date_format:H:i'],
            'day_of_week' => ['nullable', 'integer', 'min:0', 'max:6'],
            'day_of_month' => ['nullable', 'integer', 'min:1', 'max:28'],
            'retention' => ['required', 'integer', 'min:0', 'max:500'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['day_of_week'] = $data['day_of_week'] ?? 0;
        $data['day_of_month'] = $data['day_of_month'] ?? 1;
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
