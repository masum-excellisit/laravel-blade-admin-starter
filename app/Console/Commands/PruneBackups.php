<?php

namespace App\Console\Commands;

use App\Services\BackupManager;
use Illuminate\Console\Command;

class PruneBackups extends Command
{
    protected $signature = 'backup:prune {--keep= : Number of backups to keep, defaults to the configured retention}';

    protected $description = 'Delete unprotected backups beyond the retention limit';

    public function handle(BackupManager $manager): int
    {
        $keep = $this->option('keep');
        $removed = $manager->prune($keep === null ? null : (int) $keep);

        $this->info("Deleted {$removed} backup(s).");

        return self::SUCCESS;
    }
}
