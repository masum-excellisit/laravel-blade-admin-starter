<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Backup;
use App\Models\BackupSchedule;
use App\Models\Setting;
use App\Services\BackupManager;
use App\Support\Activity;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Throwable;

class BackupController extends Controller
{
    public function __construct(private readonly BackupManager $manager) {}

    public function index(Request $request)
    {
        abort_unless($request->user()->can('backups.view'), 403);

        $schedules = BackupSchedule::withCount('backups')->orderBy('name')->get();
        $stats = $this->manager->stats();

        $nextRun = $schedules->where('is_active', true)
            ->map(fn (BackupSchedule $schedule) => $schedule->nextRunAt())
            ->filter()
            ->sort()
            ->first();

        return view('admin.backups.index', [
            'backups' => Backup::with(['user', 'schedule'])
                ->when($request->filled('q'), fn ($query) => $query->where('name', 'like', '%'.$request->string('q').'%'))
                ->when($request->filled('type'), fn ($query) => $query->where('type', $request->string('type')))
                ->when($request->filled('source'), fn ($query) => $query->where('source', $request->string('source')))
                ->latest('id')
                ->paginate(15)
                ->withQueryString(),
            'stats' => $stats,
            'sources' => $this->manager->sourceSizes($request->boolean('refresh')),
            'system' => $this->manager->system(),
            'schedules' => $schedules,
            'nextRun' => $nextRun,
            'types' => BackupManager::TYPES,
            'sourceOptions' => Backup::query()->distinct()->orderBy('source')->pluck('source')->all(),
            'retention' => (int) Setting::get('backup_retention', 10),
            'alerts' => $this->alerts($stats, $schedules, $nextRun),
            'filtered' => $request->filled('q') || $request->filled('type') || $request->filled('source'),
        ]);
    }

    /**
     * Plain-language health checks shown at the top of the page.
     */
    private function alerts(array $stats, $schedules, $nextRun): array
    {
        $system = $this->manager->system();
        $alerts = [];

        if (! $system['zip']) {
            $alerts[] = ['tone' => 'error', 'title' => 'The PHP zip extension is missing',
                'body' => 'Backups cannot be created until zip is enabled in your PHP installation.'];
        }

        if (! $system['writable']) {
            $alerts[] = ['tone' => 'error', 'title' => 'The backup folder is not writable',
                'body' => 'Grant write permission to '.$stats['path'].' so archives can be saved.'];
        }

        if ($stats['disk_used_percent'] > 90) {
            $alerts[] = ['tone' => 'error', 'title' => 'The disk is almost full',
                'body' => 'Only '.BackupManager::humanBytes($stats['disk_free']).' left. Delete old backups before creating new ones.'];
        }

        if ($stats['count'] === 0) {
            $alerts[] = ['tone' => 'info', 'title' => 'No backups yet',
                'body' => 'Create your first one below — “Everything” is the safest starting point.'];
        } elseif ($stats['last']?->completed_at?->lt(now()->subDays(7))) {
            $alerts[] = ['tone' => 'warning', 'title' => 'Your last backup is over a week old',
                'body' => 'It was taken '.$stats['last']->completed_at->diffForHumans().'. Run one now or add a schedule.'];
        }

        if ($schedules->isEmpty()) {
            $alerts[] = ['tone' => 'info', 'title' => 'No automatic backups yet',
                'body' => 'Add a schedule so backups keep running without you remembering.'];
        } elseif ($nextRun && $schedules->where('is_active', true)->every(fn ($schedule) => $schedule->last_run_at === null)
            && $schedules->min('created_at') < now()->subDay()) {
            $alerts[] = ['tone' => 'warning', 'title' => 'Your schedules have never run',
                'body' => 'The server cron entry for php artisan schedule:run is probably missing — see the Schedules tab.'];
        }

        if ($stats['failed'] > 0) {
            $alerts[] = ['tone' => 'warning', 'title' => $stats['failed'].' backup(s) failed',
                'body' => 'Open the failed rows below to see why, then delete them once resolved.'];
        }

        return $alerts;
    }

    public function store(Request $request)
    {
        abort_unless($request->user()->can('backups.create'), 403);

        $data = $request->validate([
            'type' => ['required', Rule::in(array_keys(BackupManager::TYPES))],
        ]);

        $backup = $this->manager->create($data['type'], 'manual', $request->user()->id);

        if ($backup->status === 'failed') {
            return back()->with('error', 'Backup failed: '.$backup->error);
        }

        Activity::log('created', $backup, "Created a {$backup->type} backup ({$backup->name})");

        return back()->with('success', "Backup created — {$backup->name} ({$backup->size_for_humans}).");
    }

    public function download(Request $request, Backup $backup)
    {
        abort_unless($request->user()->can('backups.view'), 403);

        if (! $backup->exists()) {
            return back()->with('error', 'That backup file is no longer on disk.');
        }

        Activity::log('downloaded', $backup, "Downloaded backup {$backup->name}");

        return response()->download($backup->absolutePath(), $backup->name);
    }

    public function restore(Request $request, Backup $backup)
    {
        abort_unless($request->user()->can('backups.edit'), 403);

        $data = $request->validate([
            'parts' => ['required', 'array', 'min:1'],
            'parts.*' => [Rule::in(BackupManager::PARTS)],
            'safety_backup' => ['nullable', 'boolean'],
        ]);

        try {
            $restored = $this->manager->restore(
                $backup,
                $data['parts'],
                $request->boolean('safety_backup')
            );
        } catch (Throwable $e) {
            return back()->with('error', 'Restore failed: '.$e->getMessage());
        }

        Activity::log('restored', $backup, 'Restored '.implode(', ', $restored)." from {$backup->name}");

        return redirect()
            ->route('admin.backups.index')
            ->with('success', 'Restored '.implode(', ', $restored).' from '.$backup->name.'.');
    }

    public function upload(Request $request)
    {
        abort_unless($request->user()->can('backups.create'), 403);

        $request->validate([
            'archive' => ['required', 'file', 'mimetypes:application/zip,application/x-zip-compressed,multipart/x-zip'],
        ]);

        $file = $request->file('archive');
        $name = now()->format('Ymd-His').'-'.preg_replace('/[^A-Za-z0-9._-]/', '-', $file->getClientOriginalName());
        $name = str_ends_with($name, '.zip') ? $name : $name.'.zip';
        $target = $this->manager->directory().'/'.$name;

        $file->move($this->manager->directory(), $name);

        try {
            $backup = $this->manager->register($target, 'upload', $request->user()->id);
        } catch (Throwable $e) {
            @unlink($target);

            return back()->with('error', $e->getMessage());
        }

        Activity::log('uploaded', $backup, "Uploaded backup {$backup->name}");

        return back()->with('success', "Uploaded {$backup->name} — it is ready to restore.");
    }

    public function protect(Request $request, Backup $backup)
    {
        abort_unless($request->user()->can('backups.edit'), 403);

        $backup->update(['is_protected' => ! $backup->is_protected]);

        return back()->with('success', $backup->is_protected
            ? 'Backup locked — automatic cleanup will skip it.'
            : 'Backup unlocked.');
    }

    public function rescan(Request $request)
    {
        abort_unless($request->user()->can('backups.create'), 403);

        $added = $this->manager->syncFromDisk();

        return back()->with('success', $added === 0
            ? 'No new archives found in the backup folder.'
            : "Imported {$added} archive(s) from the backup folder.");
    }

    public function destroy(Request $request, Backup $backup)
    {
        abort_unless($request->user()->can('backups.delete'), 403);

        $name = $backup->name;
        $this->manager->delete($backup);

        Activity::log('deleted', null, "Deleted backup {$name}");

        return back()->with('success', "Deleted {$name}.");
    }

    public function prune(Request $request)
    {
        abort_unless($request->user()->can('backups.delete'), 403);

        $data = $request->validate([
            'keep' => ['required', 'integer', 'min:1', 'max:500'],
        ]);

        Setting::put('backup_retention', (string) $data['keep'], 'backups');

        $removed = $this->manager->prune($data['keep']);

        Activity::log('deleted', null, "Pruned {$removed} backup(s), keeping the newest {$data['keep']}");

        return back()->with('success', $removed === 0
            ? 'Nothing to clean up — every backup is within the limit or locked.'
            : "Deleted {$removed} old backup(s).");
    }
}
