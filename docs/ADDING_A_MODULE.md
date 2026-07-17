# Adding a Module

There are two ways to add a new admin CRUD module: the **generator** (recommended) or **by hand**.

## 1. The generator (recommended)

```bash
php artisan make:admin-module Faq
```

This scaffolds and wires up a complete, working CRUD module:

| Artifact | Location |
|----------|----------|
| Migration (`title`, `body`, `status`) | `database/migrations/*_create_faqs_table.php` |
| Model | `app/Models/Faq.php` |
| Form request | `app/Http/Requests/Admin/FaqRequest.php` |
| Controller (resource) | `app/Http/Controllers/Admin/FaqController.php` |
| Views (`index`, `create`, `edit`, `_form`) | `resources/views/admin/faqs/` |
| Route (permission-guarded) | injected into `routes/admin.php` |
| Sidebar link | injected into `resources/views/partials/admin-sidebar.blade.php` |
| Permissions (`faqs.view/create/edit/delete`) | created + granted to `super-admin` & `admin` |

The migration runs automatically. Options:

- `--no-migrate` — skip running the migration.
- `--force` — overwrite existing files.

After generating, **edit the migration/model/request** to add your real fields, then `php artisan migrate:fresh --seed` (dev) or add a new migration.

The naming is derived from the singular studly argument:

| Argument | Table / route / permission | Model | Sidebar label |
|----------|---------------------------|-------|----------------|
| `Faq` | `faqs` | `Faq` | Faqs |
| `ProductCategory` | `product_categories` | `ProductCategory` | Product Categories |

Injection markers `// [admin-module routes]` (in `routes/admin.php`, **before Settings**) and `// [admin-module nav]` (in the sidebar, **before Settings**) must remain in place for the generator to work.

Sidebar order convention:

1. Dashboard → CMS Management → content modules (Users, Services, …, Menus, Media, Messages)
2. **Generated modules are injected here** (before Settings)
3. Settings
4. Admin Users → Roles → Permissions (always last)


## 2. By hand

Follow the same pattern the built-in modules use (e.g. `Page`):

1. **Migration + model** — `app/Models/Foo.php`, add `$fillable`.
2. **FormRequest** — `app/Http/Requests/Admin/FooRequest.php` with `rules()`.
3. **Controller** — `app/Http/Controllers/Admin/FooController.php` extending `Controller`, resource methods, guard writes with `abort_unless($request->user()->can('foos.create'), 403)`.
4. **Views** — copy `resources/views/admin/pages/` as a template; reuse the shared components (`<x-card>`, `<x-btn>`, `<x-table>`, `<x-form.*>`, `<x-page-header>`).
5. **Route** — inside the `auth` group in `routes/admin.php`:
   ```php
   Route::middleware('permission:foos.view')->group(function () {
       Route::resource('foos', FooController::class)->except('show');
   });
   ```
6. **Sidebar** — add an entry to the `$nav` array in `admin-sidebar.blade.php`.
7. **Permissions** — add `foos.*` in `database/seeders/RolePermissionSeeder.php` (and re-seed) or create them at runtime.

## The Jodit editor

Add a rich editor to any textarea:

```blade
<textarea name="body" data-jodit data-upload-url="{{ route('admin.media.jodit') }}"></textarea>
```

Images dropped/uploaded go through the Media module (`media/jodit` endpoint) and are stored on the `public` disk (auto-resized to ≤1600px).
