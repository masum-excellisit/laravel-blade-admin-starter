<?php

namespace App\Console\Commands;

use App\Models\BackupSchedule;
use App\Services\BackupManager;
use Illuminate\Console\Command;

class RunBackup extends Command
{
    protected $signature = 'backup:run
        {--type=full : database, storage, code or full}
        {--source=manual : manual, scheduled or safety}
        {--schedule= : Run a saved schedule by id, using its type and retention}
        {--prune : Remove old backups beyond the retention limit afterwards}';

    protected $description = 'Create a backup archive of the database, storage and/or codebase';

    public function handle(BackupManager $manager): int
    {
        $schedule = $this->option('schedule')
            ? BackupSchedule::find((int) $this->option('schedule'))
            : null;

        if ($this->option('schedule') && ! $schedule) {
            $this->error("Schedule [{$this->option('schedule')}] no longer exists.");

            return self::FAILURE;
        }

        $type = $schedule?->type ?? $this->option('type');
        $source = $schedule ? 'scheduled' : $this->option('source');

        if (! array_key_exists($type, BackupManager::TYPES)) {
            $this->error("Unknown type [{$type}]. Use one of: ".implode(', ', array_keys(BackupManager::TYPES)));

            return self::FAILURE;
        }

        $this->info("Creating {$type} backup...");

        $backup = $manager->create($type, $source, null, $schedule?->id);
        $schedule?->update(['last_run_at' => now()]);

        if ($backup->status === 'failed') {
            $this->error("Backup failed: {$backup->error}");

            return self::FAILURE;
        }

        $this->info("Created {$backup->name} ({$backup->size_for_humans}) in {$backup->duration}.");

        if ($schedule) {
            $removed = $manager->prune($schedule->retention, $schedule->id);
            $this->info("Pruned {$removed} old backup(s) from this schedule.");
        } elseif ($this->option('prune')) {
            $removed = $manager->prune();
            $this->info("Pruned {$removed} old backup(s).");
        }

        return self::SUCCESS;
    }
}
