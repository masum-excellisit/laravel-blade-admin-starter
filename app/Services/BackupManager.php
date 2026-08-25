<?php

namespace App\Services;

use App\Models\Backup;
use App\Models\Setting;
use Illuminate\Database\Connection;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RecursiveCallbackFilterIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;
use SplFileInfo;
use Throwable;
use ZipArchive;

class BackupManager
{
    public const TYPES = [
        'database' => 'Database',
        'storage' => 'Storage',
        'code' => 'Codebase',
        'full' => 'Everything',
    ];

    public const PARTS = ['database', 'storage', 'code'];

    private const MANIFEST_ENTRY = '_backup/manifest.json';

    private const SQL_ENTRY = '_backup/database.sql';

    /**
     * Parts contained in a backup of the given type.
     */
    public function partsFor(string $type): array
    {
        return $type === 'full' ? self::PARTS : [$type];
    }

    public function directory(): string
    {
        $dir = storage_path('app/'.trim(config('backup.path'), '/'));

        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        if (! is_file($dir.'/.gitignore')) {
            file_put_contents($dir.'/.gitignore', "*\n!.gitignore\n");
        }

        return $dir;
    }

    /* -----------------------------------------------------------------
     | Creating
     | ----------------------------------------------------------------- */

    public function create(string $type, string $source = 'manual', ?int $userId = null, ?int $scheduleId = null): Backup
    {
        if (! array_key_exists($type, self::TYPES)) {
            throw new RuntimeException("Unknown backup type [{$type}].");
        }

        $this->relaxLimits();

        $parts = $this->partsFor($type);

        $prefix = Str::slug(config('app.name', 'app')).'-'.$type.'-'.now()->format('Ymd-His');
        $name = $prefix.'.zip';

        // Two backups inside the same second would otherwise collide.
        while (Backup::where('name', $name)->exists() || is_file($this->directory().'/'.$name)) {
            $name = $prefix.'-'.Str::lower(Str::random(4)).'.zip';
        }

        $relative = trim(config('backup.path'), '/').'/'.$name;

        $backup = Backup::create([
            'name' => $name,
            'type' => $type,
            'parts' => $parts,
            'path' => $relative,
            'status' => 'running',
            'source' => $source,
            'backup_schedule_id' => $scheduleId,
            'user_id' => $userId,
            'started_at' => now(),
        ]);

        try {
            $this->build($this->directory().'/'.$name, $parts);

            $backup->update([
                'status' => 'completed',
                'size' => (int) @filesize($this->directory().'/'.$name),
                'completed_at' => now(),
            ]);
        } catch (Throwable $e) {
            @unlink($this->directory().'/'.$name);

            $backup->update([
                'status' => 'failed',
                'error' => Str::limit($e->getMessage(), 2000),
                'completed_at' => now(),
            ]);
        }

        return $backup->refresh();
    }

    protected function build(string $target, array $parts): void
    {
        if (! class_exists(ZipArchive::class)) {
            throw new RuntimeException('The PHP zip extension is required to create backups.');
        }

        $zip = new ZipArchive;
        if ($zip->open($target, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException("Unable to create the archive at [{$target}].");
        }

        $sqlDump = null;

        try {
            if (in_array('database', $parts, true)) {
                $sqlDump = $this->directory().'/dump-'.Str::random(8).'.sql';
                $this->dumpDatabase($sqlDump);
                $zip->addFile($sqlDump, self::SQL_ENTRY);
            }

            if (in_array('storage', $parts, true)) {
                $root = storage_path('app');
                foreach ($this->files($root, config('backup.storage_excludes')) as $relative => $absolute) {
                    $zip->addFile($absolute, 'storage/app/'.$relative);
                }
            }

            if (in_array('code', $parts, true)) {
                $excludes = config('backup.code_excludes');
                if (! config('backup.include_env')) {
                    $excludes[] = '.env';
                }

                foreach ($this->files(base_path(), $excludes) as $relative => $absolute) {
                    $zip->addFile($absolute, $relative);
                }
            }

            $zip->addFromString(self::MANIFEST_ENTRY, json_encode($this->manifest($parts), JSON_PRETTY_PRINT));

            if (! $zip->close()) {
                throw new RuntimeException('The archive could not be written to disk.');
            }
        } catch (Throwable $e) {
            @$zip->close();
            throw $e;
        } finally {
            if ($sqlDump) {
                @unlink($sqlDump);
            }
        }
    }

    protected function manifest(array $parts): array
    {
        return [
            'app' => config('app.name'),
            'url' => config('app.url'),
            'parts' => $parts,
            'type' => count($parts) === 3 ? 'full' : $parts[0],
            'created_at' => now()->toIso8601String(),
            'php' => PHP_VERSION,
            'laravel' => app()->version(),
            'database' => [
                'connection' => config('database.default'),
                'driver' => DB::connection()->getDriverName(),
                'name' => DB::connection()->getDatabaseName(),
            ],
        ];
    }

    /**
     * Yield relative => absolute paths beneath a root, skipping excluded paths and symlinks.
     */
    protected function files(string $root, array $excludes): \Generator
    {
        $root = rtrim(str_replace('\\', '/', $root), '/');

        if (! is_dir($root)) {
            return;
        }

        $relativeTo = function (SplFileInfo $file) use ($root): string {
            return ltrim(substr(str_replace('\\', '/', $file->getPathname()), strlen($root)), '/');
        };

        $iterator = new RecursiveIteratorIterator(
            new RecursiveCallbackFilterIterator(
                new RecursiveDirectoryIterator($root, RecursiveDirectoryIterator::SKIP_DOTS),
                function (SplFileInfo $file) use ($relativeTo, $excludes): bool {
                    if ($file->isLink()) {
                        return false;
                    }

                    $relative = $relativeTo($file);

                    foreach ($excludes as $exclude) {
                        $exclude = trim($exclude, '/');
                        if ($relative === $exclude || str_starts_with($relative, $exclude.'/')) {
                            return false;
                        }
                    }

                    return true;
                }
            ),
            RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($iterator as $file) {
            if ($file->isFile() && $file->isReadable()) {
                yield $relativeTo($file) => $file->getPathname();
            }
        }
    }

    /* -----------------------------------------------------------------
     | Restoring
     | ----------------------------------------------------------------- */

    /**
     * @param  array  $parts  Any of database, storage, code.
     * @return array Parts that were restored.
     */
    public function restore(Backup $backup, array $parts, bool $safetyBackup = true): array
    {
        if (! $backup->exists()) {
            throw new RuntimeException('The backup file is missing from disk.');
        }

        $parts = array_values(array_intersect(self::PARTS, $parts, $backup->parts ?? []));

        if (empty($parts)) {
            throw new RuntimeException('Select at least one part that this backup actually contains.');
        }

        $this->relaxLimits();

        if ($safetyBackup) {
            $this->create(count($parts) === 1 ? $parts[0] : 'full', 'safety');
        }

        $zip = new ZipArchive;
        if ($zip->open($backup->absolutePath()) !== true) {
            throw new RuntimeException('The backup archive could not be opened.');
        }

        try {
            if (in_array('storage', $parts, true)) {
                $this->extract($zip, fn (string $entry) => str_starts_with($entry, 'storage/'));
            }

            if (in_array('code', $parts, true)) {
                $this->extract($zip, fn (string $entry) => ! str_starts_with($entry, 'storage/') && ! str_starts_with($entry, '_backup/'));
            }

            if (in_array('database', $parts, true)) {
                $sql = $zip->getStream(self::SQL_ENTRY);
                if (! $sql) {
                    throw new RuntimeException('This archive does not contain a database dump.');
                }

                $temp = $this->directory().'/restore-'.Str::random(8).'.sql';
                $out = fopen($temp, 'w');
                stream_copy_to_stream($sql, $out);
                fclose($out);
                fclose($sql);

                try {
                    $this->importSql($temp);
                } finally {
                    @unlink($temp);
                }
            }
        } finally {
            $zip->close();
        }

        $this->afterRestore();

        return $parts;
    }

    protected function extract(ZipArchive $zip, callable $filter): void
    {
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $entry = $zip->getNameIndex($i);

            if ($entry === false || str_ends_with($entry, '/') || ! $filter($entry)) {
                continue;
            }

            if (str_contains($entry, '..') || str_starts_with($entry, '/')) {
                continue;
            }

            $target = base_path($entry);
            $dir = dirname($target);

            if (! is_dir($dir)) {
                mkdir($dir, 0755, true);
            }

            $stream = $zip->getStream($entry);
            if (! $stream) {
                continue;
            }

            $out = fopen($target, 'w');
            stream_copy_to_stream($stream, $out);
            fclose($out);
            fclose($stream);
        }
    }

    protected function afterRestore(): void
    {
        try {
            Artisan::call('config:clear');
            Artisan::call('cache:clear');
            Artisan::call('view:clear');
            Artisan::call('route:clear');
        } catch (Throwable) {
            // Cache clearing is best effort — a restore is still valid without it.
        }

        $this->syncFromDisk();
    }

    /* -----------------------------------------------------------------
     | Database dump / import
     | ----------------------------------------------------------------- */

    public function dumpDatabase(string $target): void
    {
        $connection = DB::connection();
        $driver = $connection->getDriverName();

        $handle = fopen($target, 'w');

        fwrite($handle, "-- {$connection->getDatabaseName()} dump generated ".now()->toDateTimeString()."\n");

        try {
            match ($driver) {
                'mysql', 'mariadb' => $this->dumpMySql($connection, $handle),
                'sqlite' => $this->dumpSqlite($connection, $handle),
                'pgsql' => throw new RuntimeException('PostgreSQL dumps are not supported yet — use pg_dump.'),
                default => throw new RuntimeException("Database backups are not supported for the [{$driver}] driver."),
            };
        } finally {
            fclose($handle);
        }
    }

    protected function dumpMySql(Connection $connection, $handle): void
    {
        $skipData = config('backup.skip_table_data');

        fwrite($handle, "SET NAMES utf8mb4;\n");
        fwrite($handle, "SET FOREIGN_KEY_CHECKS = 0;\n");
        fwrite($handle, "SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';\n");

        $tables = [];
        $views = [];

        foreach ($connection->select('SHOW FULL TABLES') as $row) {
            $row = array_values((array) $row);
            if (($row[1] ?? 'BASE TABLE') === 'VIEW') {
                $views[] = $row[0];
            } else {
                $tables[] = $row[0];
            }
        }

        foreach ($tables as $table) {
            $create = (array) $connection->selectOne("SHOW CREATE TABLE `{$table}`");

            fwrite($handle, "\nDROP TABLE IF EXISTS `{$table}`;\n");
            fwrite($handle, $create['Create Table'].";\n");

            if (! in_array($table, $skipData, true)) {
                $this->writeInserts($connection, $handle, $table, 'mysql');
            }
        }

        foreach ($views as $view) {
            $create = (array) $connection->selectOne("SHOW CREATE VIEW `{$view}`");
            $sql = preg_replace('/DEFINER=[^\s]+\s/', '', $create['Create View']);

            fwrite($handle, "\nDROP VIEW IF EXISTS `{$view}`;\n");
            fwrite($handle, $sql.";\n");
        }

        fwrite($handle, "\nSET FOREIGN_KEY_CHECKS = 1;\n");
    }

    protected function dumpSqlite(Connection $connection, $handle): void
    {
        $skipData = config('backup.skip_table_data');

        fwrite($handle, "PRAGMA foreign_keys = OFF;\n");

        $objects = $connection->select(
            "SELECT type, name, sql FROM sqlite_master WHERE sql IS NOT NULL AND name NOT LIKE 'sqlite_%' ORDER BY CASE type WHEN 'table' THEN 0 ELSE 1 END"
        );

        foreach ($objects as $object) {
            $object = (array) $object;

            if ($object['type'] === 'table') {
                fwrite($handle, "\nDROP TABLE IF EXISTS \"{$object['name']}\";\n");
            } elseif ($object['type'] === 'index') {
                fwrite($handle, "\nDROP INDEX IF EXISTS \"{$object['name']}\";\n");
            } else {
                fwrite($handle, "\nDROP {$object['type']} IF EXISTS \"{$object['name']}\";\n");
            }

            fwrite($handle, preg_replace('/\s+/', ' ', $object['sql']).";\n");

            if ($object['type'] === 'table' && ! in_array($object['name'], $skipData, true)) {
                $this->writeInserts($connection, $handle, $object['name'], 'sqlite');
            }
        }

        fwrite($handle, "\nPRAGMA foreign_keys = ON;\n");
    }

    protected function writeInserts(Connection $connection, $handle, string $table, string $driver): void
    {
        $quotedTable = $this->quoteIdentifier($table, $driver);
        $chunk = max(1, (int) config('backup.insert_chunk'));
        $rows = [];
        $columns = null;

        foreach ($connection->cursor("SELECT * FROM {$quotedTable}") as $row) {
            $row = (array) $row;

            $columns ??= implode(', ', array_map(fn ($c) => $this->quoteIdentifier($c, $driver), array_keys($row)));

            $rows[] = '('.implode(', ', array_map(fn ($v) => $this->quoteValue($v, $driver), $row)).')';

            if (count($rows) >= $chunk) {
                fwrite($handle, "INSERT INTO {$quotedTable} ({$columns}) VALUES ".implode(', ', $rows).";\n");
                $rows = [];
            }
        }

        if ($rows) {
            fwrite($handle, "INSERT INTO {$quotedTable} ({$columns}) VALUES ".implode(', ', $rows).";\n");
        }
    }

    protected function quoteIdentifier(string $name, string $driver): string
    {
        return $driver === 'sqlite'
            ? '"'.str_replace('"', '""', $name).'"'
            : '`'.str_replace('`', '``', $name).'`';
    }

    protected function quoteValue(mixed $value, string $driver): string
    {
        if ($value === null) {
            return 'NULL';
        }

        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        if (is_int($value) || is_float($value)) {
            return (string) $value;
        }

        $value = (string) $value;

        if (! mb_check_encoding($value, 'UTF-8')) {
            return $driver === 'sqlite' ? "X'".bin2hex($value)."'" : '0x'.bin2hex($value);
        }

        if ($driver === 'sqlite') {
            return "'".str_replace("'", "''", $value)."'";
        }

        return "'".str_replace(
            ['\\', "\0", "\n", "\r", "'", '"', "\x1a"],
            ['\\\\', '\\0', '\\n', '\\r', "\\'", '\\"', '\\Z'],
            $value
        )."'";
    }

    public function importSql(string $path): void
    {
        $connection = DB::connection();
        $driver = $connection->getDriverName();

        if ($driver === 'sqlite') {
            $connection->statement('PRAGMA foreign_keys = OFF');
        } else {
            $connection->statement('SET FOREIGN_KEY_CHECKS = 0');
        }

        try {
            foreach ($this->statements($path, $driver) as $statement) {
                $connection->unprepared($statement);
            }
        } finally {
            if ($driver === 'sqlite') {
                $connection->statement('PRAGMA foreign_keys = ON');
            } else {
                $connection->statement('SET FOREIGN_KEY_CHECKS = 1');
            }
        }
    }

    /**
     * Split a dump into executable statements, honouring quoted strings and comments.
     */
    protected function statements(string $path, string $driver): \Generator
    {
        $handle = fopen($path, 'r');
        if (! $handle) {
            throw new RuntimeException('The SQL dump could not be read.');
        }

        $backslashEscapes = $driver !== 'sqlite';
        $buffer = '';
        $quote = null;
        $escaped = false;

        try {
            while (($line = fgets($handle)) !== false) {
                $length = strlen($line);

                for ($i = 0; $i < $length; $i++) {
                    $char = $line[$i];

                    if ($quote !== null) {
                        $buffer .= $char;

                        if ($escaped) {
                            $escaped = false;
                        } elseif ($backslashEscapes && $char === '\\') {
                            $escaped = true;
                        } elseif ($char === $quote) {
                            $quote = null;
                        }

                        continue;
                    }

                    if (($char === '-' && ($line[$i + 1] ?? '') === '-' && trim($buffer) === '') || ($char === '#' && trim($buffer) === '')) {
                        break; // Comment runs to the end of the line.
                    }

                    if ($char === "'" || $char === '"' || $char === '`') {
                        $quote = $char;
                        $buffer .= $char;

                        continue;
                    }

                    if ($char === ';') {
                        $statement = trim($buffer);
                        $buffer = '';

                        if ($statement !== '') {
                            yield $statement;
                        }

                        continue;
                    }

                    $buffer .= $char;
                }
            }

            if (trim($buffer) !== '') {
                yield trim($buffer);
            }
        } finally {
            fclose($handle);
        }
    }

    /* -----------------------------------------------------------------
     | Library maintenance
     | ----------------------------------------------------------------- */

    /**
     * Register archives that exist on disk but are missing from the database.
     */
    public function syncFromDisk(): int
    {
        $known = Backup::pluck('name')->all();
        $added = 0;

        foreach (glob($this->directory().'/*.zip') ?: [] as $file) {
            if (in_array(basename($file), $known, true)) {
                continue;
            }

            $this->register($file, 'imported');
            $added++;
        }

        return $added;
    }

    /**
     * Create a database record for an archive already sitting in the backup folder.
     */
    public function register(string $absolutePath, string $source = 'imported', ?int $userId = null): Backup
    {
        $manifest = $this->readManifest($absolutePath);
        $parts = array_values(array_intersect(self::PARTS, $manifest['parts'] ?? []));

        if (empty($parts)) {
            throw new RuntimeException('This archive is not a valid backup — its manifest is missing or empty.');
        }

        return Backup::updateOrCreate(
            ['name' => basename($absolutePath)],
            [
                'type' => count($parts) === 3 ? 'full' : $parts[0],
                'parts' => $parts,
                'path' => trim(config('backup.path'), '/').'/'.basename($absolutePath),
                'size' => (int) filesize($absolutePath),
                'status' => 'completed',
                'source' => $source,
                'user_id' => $userId,
                'started_at' => isset($manifest['created_at']) ? date('Y-m-d H:i:s', strtotime($manifest['created_at'])) : now(),
                'completed_at' => isset($manifest['created_at']) ? date('Y-m-d H:i:s', strtotime($manifest['created_at'])) : now(),
            ]
        );
    }

    public function readManifest(string $absolutePath): array
    {
        $zip = new ZipArchive;

        if ($zip->open($absolutePath) !== true) {
            throw new RuntimeException('The archive could not be opened.');
        }

        $json = $zip->getFromName(self::MANIFEST_ENTRY);
        $zip->close();

        return $json ? (json_decode($json, true) ?: []) : [];
    }

    public function delete(Backup $backup): void
    {
        if ($backup->exists()) {
            @unlink($backup->absolutePath());
        }

        $backup->delete();
    }

    /**
     * Drop the oldest unprotected backups beyond the retention limit.
     * Scoped to one schedule when $scheduleId is given.
     */
    public function prune(?int $keep = null, ?int $scheduleId = null): int
    {
        $keep = $keep ?? (int) Setting::get('backup_retention', 10);

        if ($keep < 1) {
            return 0;
        }

        $expired = Backup::query()
            ->where('is_protected', false)
            ->where('status', 'completed')
            ->when($scheduleId, fn ($query) => $query->where('backup_schedule_id', $scheduleId))
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->skip($keep)
            ->take(PHP_INT_MAX)
            ->get();

        foreach ($expired as $backup) {
            $this->delete($backup);
        }

        return $expired->count();
    }

    /* -----------------------------------------------------------------
     | Reporting
     | ----------------------------------------------------------------- */

    /**
     * Real numbers measured from disk and the database — never estimates.
     */
    public function stats(): array
    {
        $dir = $this->directory();

        $onDisk = 0;
        $archives = 0;
        foreach (glob($dir.'/*.zip') ?: [] as $file) {
            $onDisk += (int) filesize($file);
            $archives++;
        }

        $total = (float) (@disk_total_space($dir) ?: 0);
        $free = (float) (@disk_free_space($dir) ?: 0);

        return [
            'count' => Backup::where('status', 'completed')->count(),
            'failed' => Backup::where('status', 'failed')->count(),
            'archives' => $archives,
            'archive_bytes' => $onDisk,
            'last' => Backup::where('status', 'completed')->latest('completed_at')->first(),
            'disk_total' => $total,
            'disk_free' => $free,
            'disk_used' => max(0, $total - $free),
            'disk_used_percent' => $total > 0 ? round((($total - $free) / $total) * 100, 1) : 0.0,
            'path' => $dir,
        ];
    }

    /**
     * Live size of each backup source. Cached briefly — walking the project is not free.
     */
    public function sourceSizes(bool $fresh = false): array
    {
        $key = 'backup.source-sizes';

        if ($fresh) {
            Cache::forget($key);
        }

        return Cache::remember($key, now()->addMinutes(10), fn () => [
            'database' => $this->databaseSize(),
            'storage' => $this->directorySize(storage_path('app'), config('backup.storage_excludes')),
            'code' => $this->directorySize(base_path(), config('backup.code_excludes')),
            'measured_at' => now()->toDateTimeString(),
        ]);
    }

    /**
     * Size of the live database, detected from whichever driver is configured.
     */
    public function databaseSize(): int
    {
        $connection = DB::connection();

        try {
            return (int) match ($connection->getDriverName()) {
                'mysql', 'mariadb' => $connection->selectOne(
                    'SELECT COALESCE(SUM(data_length + index_length), 0) AS bytes FROM information_schema.tables WHERE table_schema = ?',
                    [$connection->getDatabaseName()]
                )->bytes,
                'pgsql' => $connection->selectOne('SELECT pg_database_size(current_database()) AS bytes')->bytes,
                'sqlite' => is_file($connection->getDatabaseName()) ? filesize($connection->getDatabaseName()) : 0,
                default => 0,
            };
        } catch (Throwable) {
            return 0;
        }
    }

    public function directorySize(string $root, array $excludes = []): int
    {
        $bytes = 0;

        foreach ($this->files($root, $excludes) as $absolute) {
            $bytes += (int) @filesize($absolute);
        }

        return $bytes;
    }

    /**
     * Environment facts, auto-detected so the panel is honest on any OS or host.
     */
    public function system(): array
    {
        $connection = DB::connection();
        $driver = $connection->getDriverName();

        try {
            $version = $connection->getPdo()->getAttribute(\PDO::ATTR_SERVER_VERSION);
        } catch (Throwable) {
            $version = 'unknown';
        }

        return [
            'os' => PHP_OS_FAMILY,
            'machine' => php_uname('s').' '.php_uname('r').' ('.php_uname('m').')',
            'hostname' => php_uname('n'),
            'server' => $_SERVER['SERVER_SOFTWARE'] ?? 'CLI / unknown',
            'sapi' => PHP_SAPI,
            'php' => PHP_VERSION,
            'laravel' => app()->version(),
            'database' => strtoupper($driver).' '.$version,
            'database_name' => $connection->getDatabaseName(),
            'zip' => class_exists(ZipArchive::class),
            'memory_limit' => ini_get('memory_limit') ?: 'unknown',
            'max_execution_time' => (int) ini_get('max_execution_time'),
            'writable' => is_writable($this->directory()),
            'separator' => DIRECTORY_SEPARATOR,
        ];
    }

    public static function humanBytes(float|int $bytes, int $precision = 1): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];
        $bytes = max(0, (float) $bytes);
        $i = 0;

        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, $i === 0 ? 0 : $precision).' '.$units[$i];
    }

    protected function relaxLimits(): void
    {
        @set_time_limit(0);
        @ini_set('memory_limit', '1024M');
        ignore_user_abort(true);
    }
}
