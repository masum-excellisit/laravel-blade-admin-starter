<?php

use App\Models\BackupSchedule;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Schema;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Automatic backups — every active row in backup_schedules becomes its own cron
// entry. Wrapped because the console boots before migrations have run.
try {
    if (Schema::hasTable('backup_schedules')) {
        foreach (BackupSchedule::where('is_active', true)->get() as $schedule) {
            Schedule::command("backup:run --schedule={$schedule->id}")
                ->cron($schedule->cronExpression())
                ->name("backup-schedule-{$schedule->id}")
                ->withoutOverlapping()
                ->runInBackground();
        }
    }
} catch (Throwable) {
    // No database yet — skip scheduling rather than breaking every artisan call.
}
