<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class MakeAdminModule extends Command
{
    protected $signature = 'make:admin-module {name : Singular studly model name, e.g. Faq}
        {--force : Overwrite existing files}
        {--no-migrate : Skip running the migration}';

    protected $description = 'Scaffold a complete admin CRUD module (migration, model, controller, request, views, route, sidebar entry, permissions).';

    protected string $icon = 'M4 6h16M4 10h16M4 14h10M4 18h10';

    public function handle(): int
    {
        $model = Str::studly(Str::singular($this->argument('name')));
        $r = [
            '{{ Model }}' => $model,
            '{{ table }}' => $table = Str::snake(Str::plural($model)),
            '{{ singularVar }}' => Str::camel($model),
            '{{ pluralVar }}' => Str::camel(Str::plural($model)),
            '{{ permission }}' => $table,
            '{{ Title }}' => $title = Str::headline(Str::plural($model)),
            '{{ titleLower }}' => Str::lower($title),
        ];

        $stub = base_path('stubs/admin-module');

        $files = [
            "$stub/model.stub" => app_path("Models/{$model}.php"),
            "$stub/request.stub" => app_path("Http/Requests/Admin/{$model}Request.php"),
            "$stub/controller.stub" => app_path("Http/Controllers/Admin/{$model}Controller.php"),
            "$stub/views/index.stub" => resource_path("views/admin/{$table}/index.blade.php"),
            "$stub/views/create.stub" => resource_path("views/admin/{$table}/create.blade.php"),
            "$stub/views/edit.stub" => resource_path("views/admin/{$table}/edit.blade.php"),
            "$stub/views/form.stub" => resource_path("views/admin/{$table}/_form.blade.php"),
        ];

        foreach ($files as $from => $to) {
            $this->render($from, $to, $r);
        }

        // migration (timestamped)
        $migration = database_path('migrations/'.date('Y_m_d_His')."_create_{$table}_table.php");
        $this->render("$stub/migration.stub", $migration, $r, force: true);

        $this->injectRoute($table, $model);
        $this->injectNav($table, $title);
        $this->createPermissions($table);

        if (! $this->option('no-migrate')) {
            $this->call('migrate');
        }
        Artisan::call('optimize:clear');

        $this->newLine();
        $this->info("✔ Admin module [{$model}] created.");
        $this->line("  → Visit /admin/{$table}");
        $this->line('  → Customize the schema in the new migration + model/request as needed.');

        return self::SUCCESS;
    }

    protected function render(string $from, string $to, array $r, bool $force = false): void
    {
        if (file_exists($to) && ! $force && ! $this->option('force')) {
            $this->warn('• skipped (exists): '.str_replace(base_path().'/', '', $to));

            return;
        }
        @mkdir(dirname($to), 0755, true);
        file_put_contents($to, strtr(file_get_contents($from), $r));
        $this->line('• created '.str_replace(base_path().'/', '', $to));
    }

    protected function injectRoute(string $table, string $model): void
    {
        $file = base_path('routes/admin.php');
        $marker = '// [admin-module routes]';
        $block = <<<PHP
    Route::middleware('permission:{$table}.view')->group(function () {
            Route::post('{$table}/bulk', [\\App\\Http\\Controllers\\Admin\\{$model}Controller::class, 'bulk'])->name('{$table}.bulk');
            Route::resource('{$table}', \\App\\Http\\Controllers\\Admin\\{$model}Controller::class)->except('show');
        });

        {$marker}
    PHP;
        $contents = file_get_contents($file);
        if (str_contains($contents, "resource('{$table}'")) {
            return;
        }
        file_put_contents($file, str_replace($marker, trim($block), $contents));
        $this->line('• route injected');
    }

    protected function injectNav(string $table, string $title): void
    {
        $file = resource_path('views/partials/admin-sidebar.blade.php');
        $marker = '// [admin-module nav]';
        $entry = "['label' => '{$title}', 'route' => 'admin.{$table}.index', 'icon' => '{$this->icon}', 'can' => '{$table}.view'],\n    {$marker}";
        $contents = file_get_contents($file);
        if (str_contains($contents, "'admin.{$table}.index'")) {
            return;
        }
        file_put_contents($file, str_replace($marker, $entry, $contents));
        $this->line('• sidebar entry injected');
    }

    protected function createPermissions(string $table): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        foreach (['view', 'create', 'edit', 'delete'] as $action) {
            Permission::firstOrCreate(['name' => "{$table}.{$action}", 'guard_name' => 'web']);
        }
        foreach (['super-admin', 'admin'] as $roleName) {
            if ($role = Role::where('name', $roleName)->first()) {
                $role->givePermissionTo(["{$table}.view", "{$table}.create", "{$table}.edit", "{$table}.delete"]);
            }
        }
        $this->line('• permissions created');
    }
}
