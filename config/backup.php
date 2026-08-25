<?php

return [
    // Where backup archives live (relative to storage/app).
    'path' => 'backups',

    // Include the .env file inside codebase backups. Archives stay in private
    // storage, but anyone allowed to download a backup can read these secrets.
    'include_env' => env('BACKUP_INCLUDE_ENV', true),

    // Paths (relative to the project root) skipped by codebase backups.
    'code_excludes' => [
        '.git',
        '.github/workflows/cache',
        'node_modules',
        'vendor',
        'storage',
        'public/storage',
        'bootstrap/cache',
        '.phpunit.cache',
        '.idea',
        '.vscode',
        '.DS_Store',
    ],

    // Paths (relative to storage/app) skipped by storage backups.
    'storage_excludes' => [
        'backups',
        'temp',
        'framework',
        'livewire-tmp',
    ],

    // Tables whose structure is dumped without any rows.
    'skip_table_data' => [
        'cache',
        'cache_locks',
        'sessions',
        'jobs',
        'job_batches',
        'failed_jobs',
    ],

    // Rows per multi-row INSERT statement in SQL dumps.
    'insert_chunk' => 200,
];
