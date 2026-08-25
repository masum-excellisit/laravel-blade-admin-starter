<?php

namespace Tests\Feature;

use App\Models\Backup;
use App\Models\BackupSchedule;
use App\Models\Page;
use App\Models\User;
use App\Services\BackupManager;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;
use ZipArchive;

class BackupTest extends TestCase
{
    use RefreshDatabase;

    private string $sandbox;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sandbox = sys_get_temp_dir().'/backup-test-'.uniqid();

        foreach (['app', 'config', 'vendor', 'node_modules', 'storage/app/public'] as $dir) {
            mkdir($this->sandbox.'/'.$dir, 0755, true);
        }

        file_put_contents($this->sandbox.'/app/Widget.php', '<?php // original');
        file_put_contents($this->sandbox.'/config/widget.php', '<?php return [];');
        file_put_contents($this->sandbox.'/vendor/library.php', '<?php // vendor');
        file_put_contents($this->sandbox.'/node_modules/pkg.js', 'module.exports = 1;');
        file_put_contents($this->sandbox.'/storage/app/public/note.txt', 'original upload');

        $this->app->setBasePath($this->sandbox);
        $this->app->useStoragePath($this->sandbox.'/storage');
    }

    protected function tearDown(): void
    {
        // Plain PHP so the suite behaves the same on Windows, macOS and Linux.
        if (is_dir($this->sandbox)) {
            $files = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($this->sandbox, \FilesystemIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::CHILD_FIRST
            );

            foreach ($files as $file) {
                $file->isDir() ? @rmdir($file->getPathname()) : @unlink($file->getPathname());
            }

            @rmdir($this->sandbox);
        }

        parent::tearDown();
    }

    private function manager(): BackupManager
    {
        return app(BackupManager::class);
    }

    private function superAdmin(): User
    {
        $this->seed(RolePermissionSeeder::class);

        $admin = User::factory()->create();
        $admin->assignRole(Role::findByName('super-admin'));

        return $admin;
    }

    private function entries(Backup $backup): array
    {
        $zip = new ZipArchive;
        $zip->open($backup->absolutePath());

        $names = [];
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $names[] = $zip->getNameIndex($i);
        }
        $zip->close();

        return $names;
    }

    public function test_full_backup_contains_database_storage_and_code_and_skips_dependencies(): void
    {
        $backup = $this->manager()->create('full');

        $this->assertSame('completed', $backup->status, (string) $backup->error);
        $this->assertSame(['database', 'storage', 'code'], $backup->parts);
        $this->assertTrue($backup->exists());
        $this->assertGreaterThan(0, $backup->size);

        $entries = $this->entries($backup);

        $this->assertContains('_backup/manifest.json', $entries);
        $this->assertContains('_backup/database.sql', $entries);
        $this->assertContains('app/Widget.php', $entries);
        $this->assertContains('config/widget.php', $entries);
        $this->assertContains('storage/app/public/note.txt', $entries);
        $this->assertNotContains('vendor/library.php', $entries);
        $this->assertNotContains('node_modules/pkg.js', $entries);
        $this->assertNotContains('storage/app/backups/'.$backup->name, $entries);
    }

    public function test_database_only_backup_restores_deleted_rows(): void
    {
        Page::create(['title' => 'Before Backup', 'slug' => 'before-backup', 'content' => 'Hi', 'status' => 'published']);

        $backup = $this->manager()->create('database');
        $this->assertSame('completed', $backup->status, (string) $backup->error);

        Page::query()->delete();
        Page::create(['title' => 'After Backup', 'slug' => 'after-backup', 'content' => 'Nope', 'status' => 'draft']);

        $this->manager()->restore($backup, ['database'], safetyBackup: false);

        $this->assertDatabaseHas('pages', ['slug' => 'before-backup']);
        $this->assertDatabaseMissing('pages', ['slug' => 'after-backup']);
    }

    public function test_restore_puts_back_code_and_storage_files(): void
    {
        $backup = $this->manager()->create('full');

        file_put_contents($this->sandbox.'/app/Widget.php', '<?php // broken');
        unlink($this->sandbox.'/storage/app/public/note.txt');

        $restored = $this->manager()->restore($backup, ['storage', 'code'], safetyBackup: false);

        $this->assertSame(['storage', 'code'], $restored);
        $this->assertSame('<?php // original', file_get_contents($this->sandbox.'/app/Widget.php'));
        $this->assertSame('original upload', file_get_contents($this->sandbox.'/storage/app/public/note.txt'));
    }

    public function test_restore_can_create_a_safety_backup_first(): void
    {
        $backup = $this->manager()->create('storage');

        $this->manager()->restore($backup, ['storage'], safetyBackup: true);

        $this->assertDatabaseHas('backups', ['source' => 'safety', 'type' => 'storage']);
    }

    public function test_restore_rejects_parts_the_archive_does_not_contain(): void
    {
        $backup = $this->manager()->create('storage');

        $this->expectExceptionMessage('Select at least one part');

        $this->manager()->restore($backup, ['database'], safetyBackup: false);
    }

    public function test_sync_from_disk_registers_archives_missing_from_the_database(): void
    {
        $backup = $this->manager()->create('database');
        $backup->delete();

        $this->assertSame(1, $this->manager()->syncFromDisk());
        $this->assertDatabaseHas('backups', ['source' => 'imported', 'type' => 'database']);
    }

    public function test_prune_keeps_the_newest_and_never_deletes_locked_backups(): void
    {
        $locked = $this->manager()->create('database');
        $locked->forceFill(['is_protected' => true, 'created_at' => now()->subDays(10)])->save();

        $old = $this->manager()->create('database');
        $old->forceFill(['created_at' => now()->subDays(5)])->save();

        $middle = $this->manager()->create('database');
        $middle->forceFill(['created_at' => now()->subDays(3)])->save();

        $newest = $this->manager()->create('database');

        // Locked backups are pinned and do not use up the retention budget.
        $this->assertSame(1, $this->manager()->prune(2));
        $this->assertDatabaseMissing('backups', ['id' => $old->id]);
        $this->assertDatabaseHas('backups', ['id' => $middle->id]);
        $this->assertDatabaseHas('backups', ['id' => $locked->id]);
        $this->assertDatabaseHas('backups', ['id' => $newest->id]);
        $this->assertFalse(file_exists($old->absolutePath()));
    }

    public function test_admin_can_create_download_and_delete_backups_through_the_ui(): void
    {
        $admin = $this->superAdmin();

        $this->actingAs($admin)->get(route('admin.backups.index'))->assertOk();

        $this->actingAs($admin)
            ->post(route('admin.backups.store'), ['type' => 'database'])
            ->assertRedirect()
            ->assertSessionHas('success');

        $backup = Backup::firstOrFail();
        $this->assertSame('completed', $backup->status, (string) $backup->error);

        $this->actingAs($admin)
            ->get(route('admin.backups.index'))
            ->assertOk()
            ->assertSee($backup->name)
            ->assertSee(route('admin.backups.download', $backup))
            ->assertSee('Restore from backup');

        $this->actingAs($admin)
            ->get(route('admin.backups.download', $backup))
            ->assertOk()
            ->assertHeader('content-disposition', 'attachment; filename='.$backup->name);

        $this->actingAs($admin)
            ->delete(route('admin.backups.destroy', $backup))
            ->assertRedirect();

        $this->assertDatabaseCount('backups', 0);
        $this->assertFalse(file_exists($backup->absolutePath()));
    }

    public function test_multiple_schedules_can_be_created_edited_and_deleted(): void
    {
        $admin = $this->superAdmin();

        $this->actingAs($admin)->post(route('admin.backups.schedules.store'), [
            'name' => 'Nightly database',
            'type' => 'database',
            'frequency' => 'daily',
            'time' => '02:30',
            'retention' => 14,
            'is_active' => '1',
        ])->assertRedirect();

        $this->actingAs($admin)->post(route('admin.backups.schedules.store'), [
            'name' => 'Weekly everything',
            'type' => 'full',
            'frequency' => 'weekly',
            'time' => '04:00',
            'day_of_week' => 6,
            'retention' => 4,
            'is_active' => '1',
        ])->assertRedirect();

        $this->assertDatabaseCount('backup_schedules', 2);

        $nightly = BackupSchedule::where('name', 'Nightly database')->firstOrFail();
        $weekly = BackupSchedule::where('name', 'Weekly everything')->firstOrFail();

        $this->assertSame('30 2 * * *', $nightly->cronExpression());
        $this->assertSame('0 4 * * 6', $weekly->cronExpression());
        $this->assertNotNull($weekly->nextRunAt());

        $this->actingAs($admin)->get(route('admin.backups.index'))
            ->assertOk()
            ->assertSee('Nightly database')
            ->assertSee('Weekly everything')
            ->assertSee('Every Saturday at 04:00')
            ->assertSee('Every day at 02:30');

        $this->actingAs($admin)->put(route('admin.backups.schedules.update', $nightly), [
            'name' => 'Nightly database',
            'type' => 'database',
            'frequency' => 'hourly',
            'time' => '02:30',
            'retention' => 20,
        ])->assertRedirect();

        $nightly->refresh();
        $this->assertSame('hourly', $nightly->frequency);
        $this->assertSame(20, $nightly->retention);
        $this->assertFalse($nightly->is_active, 'Unchecked is_active must turn the schedule off.');

        $this->actingAs($admin)->post(route('admin.backups.schedules.toggle', $nightly))->assertRedirect();
        $this->assertTrue($nightly->refresh()->is_active);

        $this->actingAs($admin)->delete(route('admin.backups.schedules.destroy', $weekly))->assertRedirect();
        $this->assertDatabaseCount('backup_schedules', 1);
    }

    public function test_running_a_schedule_tags_the_backup_and_prunes_only_its_own(): void
    {
        $admin = $this->superAdmin();

        $schedule = BackupSchedule::create([
            'name' => 'Hourly database',
            'type' => 'database',
            'frequency' => 'hourly',
            'time' => '00:00',
            'retention' => 1,
        ]);

        $manual = $this->manager()->create('database');

        $this->actingAs($admin)->post(route('admin.backups.schedules.run', $schedule))->assertRedirect();
        $this->actingAs($admin)->post(route('admin.backups.schedules.run', $schedule))->assertRedirect();

        $this->assertNotNull($schedule->refresh()->last_run_at);
        $this->assertSame(1, $schedule->backups()->count(), 'Retention of 1 keeps a single backup for this schedule.');
        // Manual backups are untouched by schedule pruning.
        $this->assertDatabaseHas('backups', ['id' => $manual->id]);
    }

    public function test_prune_endpoint_deletes_old_backups(): void
    {
        $admin = $this->superAdmin();

        $this->manager()->create('database');
        $newest = $this->manager()->create('database');

        $this->actingAs($admin)->post(route('admin.backups.prune'), ['keep' => 1])->assertRedirect();

        $this->assertDatabaseCount('backups', 1);
        $this->assertDatabaseHas('backups', ['id' => $newest->id]);
    }

    public function test_stats_and_system_report_real_measurements(): void
    {
        $backup = $this->manager()->create('database');
        $stats = $this->manager()->stats();

        $this->assertSame(1, $stats['archives']);
        $this->assertSame((int) filesize($backup->absolutePath()), $stats['archive_bytes']);
        $this->assertGreaterThan(0, $stats['disk_total']);
        $this->assertGreaterThan(0, $stats['disk_free']);
        $this->assertSame(
            round((($stats['disk_total'] - $stats['disk_free']) / $stats['disk_total']) * 100, 1),
            $stats['disk_used_percent']
        );

        $sources = $this->manager()->sourceSizes(fresh: true);
        $this->assertGreaterThan(0, $sources['storage'], 'The sandbox contains an upload.');
        $this->assertGreaterThan(0, $sources['code'], 'The sandbox contains source files.');

        $system = $this->manager()->system();
        $this->assertSame(PHP_OS_FAMILY, $system['os']);
        $this->assertSame(PHP_VERSION, $system['php']);
        $this->assertTrue($system['zip']);
        $this->assertTrue($system['writable']);
        $this->assertStringContainsString('SQLITE', $system['database']);
    }

    public function test_users_without_permission_cannot_reach_backups(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $editor = User::factory()->create();
        $editor->assignRole(Role::findByName('editor'));

        $this->actingAs($editor)->get(route('admin.backups.index'))->assertForbidden();
        $this->actingAs($editor)->post(route('admin.backups.store'), ['type' => 'full'])->assertForbidden();
        $this->actingAs($editor)->post(route('admin.backups.schedules.store'), [
            'name' => 'Sneaky', 'type' => 'full', 'frequency' => 'daily', 'time' => '01:00', 'retention' => 5,
        ])->assertForbidden();
    }
}
