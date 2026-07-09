# Laravel 12 Blade Admin Starter — Implementation Plan

## Context
The repo (`laravel-blade-admin-starter`) is empty except for `.git`. Goal: a **reusable Laravel 12 (PHP 8.2+) website + custom admin starter kit** that can be cloned to build any client site. It ships a public landing site (static + dynamic pages) and a premium, gradient, mobile-responsive **custom Blade admin panel** with auth, multi-admin roles, permission management, profile, dashboard, users, settings (general/mail/theme), a rich Jodit editor, dynamic content modules (Pages, Blog, Menus), and a documented pattern/generator to add new modules.

**Locked decisions:** Tailwind CSS + Alpine.js (Vite) · Custom Blade auth + `spatie/laravel-permission` · MySQL · CMS scope = Pages + Blog/Posts + Menus + generic module scaffold.

Environment verified: PHP 8.3.14, Composer 2.8.12, Node 25.2.1, npm 11.6.2.

---

## Tech Stack
- **Laravel 12**, PHP 8.2+ (`composer create-project laravel/laravel .`)
- **Frontend:** Tailwind CSS v4 + Alpine.js, bundled via **Vite**
- **Auth:** custom Blade controllers/views (no Breeze scaffold), Laravel's built-in `Auth`/guards
- **Roles/permissions:** `spatie/laravel-permission`
- **Editor:** Jodit (via npm `jodit`, wired through Vite)
- **Icons:** Heroicons (inline SVG blade components) or Lucide
- **DB:** MySQL
- **Helpers:** `spatie/laravel-permission`, `intervention/image` (image resize for media), optional `laravel/pint` for formatting

---

## Phase 1 — Project Bootstrap
1. `composer create-project laravel/laravel .` in the current dir (empty repo).
2. Configure `.env`: `DB_CONNECTION=mysql`, DB name `laravel_blade_admin_starter`, app name, `APP_URL`.
3. Install Node deps + Tailwind v4 + Alpine + Jodit; configure `vite.config.js`, `resources/css/app.css` (Tailwind + custom gradient theme tokens as CSS variables), `resources/js/app.js` (register Alpine + Jodit initializer).
4. `composer require spatie/laravel-permission intervention/image`; publish permission config + migration.
5. Create two guard layouts: `resources/views/layouts/app.blade.php` (public site) and `resources/views/layouts/admin.blade.php` (admin shell: sidebar, topbar, theme-driven).

## Phase 2 — Data Model & Migrations
Migrations + Eloquent models:
- **users** (extend default: add `avatar`, `phone`, `status`, `last_login_at`). Assign roles via spatie.
- **spatie tables** (roles, permissions, pivots) from published migration.
- **settings** — key/value with `group` (`general`|`mail`|`theme`) + `type` for a generic settings store; cached accessor.
- **pages** — `title`, `slug`, `body` (Jodit HTML), `meta_title`, `meta_description`, `status`, `template`, `is_static` flag.
- **posts** — `title`, `slug`, `excerpt`, `body`, `featured_image`, `category_id`, `status`, `published_at`, `author_id`.
- **categories** — `name`, `slug`, `parent_id`.
- **menus** + **menu_items** — dynamic navigation builder (label, url/route/page link, parent, order).
- **media** — uploaded files metadata (path, mime, size, disk) for Jodit + featured images.
- **activity/login audit** (optional, light): `last_login_at` on users covers the minimum.

Seeders:
- `RolePermissionSeeder` — roles: `super-admin`, `admin`, `editor`; granular permissions per module (`users.view/create/edit/delete`, `roles.*`, `pages.*`, `posts.*`, `settings.*`, `menus.*`). `super-admin` gets all via Gate::before.
- `AdminUserSeeder` — seed a super-admin login.
- `SettingsSeeder` — default general/mail/theme values (gradient palette, logo placeholders).
- `DemoContentSeeder` — sample pages, posts, categories, a menu.

## Phase 3 — Authentication (custom Blade)
- Routes: `GET/POST /admin/login`, `POST /admin/logout`, plus password reset (forgot/reset) using Laravel's built-in broker.
- `Admin\Auth\LoginController` with rate limiting + `last_login_at` update.
- Premium gradient login view (`resources/views/admin/auth/login.blade.php`).
- `admin` route group middleware: `auth` + a `role_or_permission` gate check; redirect guests to admin login.
- Public routes stay open.

## Phase 4 — Admin Panel Core (Blade + Tailwind + Alpine)
Admin shell: collapsible sidebar (mobile drawer via Alpine), topbar (profile dropdown, theme toggle), breadcrumb, flash-toast component, reusable Blade components (`x-card`, `x-btn`, `x-form.input`, `x-table`, `x-modal`, `x-badge`, `x-page-header`). All theme colors driven by CSS variables set from **theme settings** so a client can re-skin without code.

Modules (each = controller + FormRequest + Blade index/create/edit + policy/permission gate):
1. **Dashboard** — stat cards (users, pages, posts counts), recent activity, gradient widgets.
2. **Users** — CRUD, assign roles, avatar upload, status toggle, search/filter/pagination.
3. **Roles** — CRUD + permission matrix assignment UI (checkbox grid grouped by module).
4. **Permissions** — CRUD/management (guarded; usually managed via seeder but editable).
5. **Profile** — edit own details, change password, avatar.
6. **Pages** — CRUD with **Jodit** body editor, slug auto-gen, SEO meta, status, template picker.
7. **Posts (Blog)** — CRUD with Jodit, categories, featured image (media picker), publish scheduling.
8. **Categories** — CRUD (nested).
9. **Menus** — menu + drag-order menu-item builder (Alpine sortable), link to pages/posts/custom URLs.
10. **Media** — upload/list/delete; endpoint that Jodit uses for image insertion (Intervention resize).
11. **Settings** — tabbed:
    - *General*: site name, logo, favicon, contact info, social links.
    - *Mail*: SMTP host/port/user/pass/from — persisted to DB, applied at runtime via a config override + a "send test email" button.
    - *Theme*: primary/secondary gradient colors, sidebar style, light/dark default, font — writes CSS variables consumed by both admin + public layouts.

## Phase 5 — Public Site (landing + dynamic)
- **Landing page** with premium gradient hero, sections, pulling site name/logo/theme from settings.
- **Static pages** — About, Contact (with form → stored/emailed), etc. as Blade views.
- **Dynamic pages** — `PageController@show` renders DB `pages` by slug with chosen template.
- **Blog** — index, category, single post from DB.
- **Dynamic navigation** — header/footer menus rendered from the Menus module.
- Public layout reads theme settings so the whole site restyles per client.

## Phase 6 — Reusability / Extensibility
- **Module generator:** an artisan command `make:admin-module {Name}` (custom generator) that stubs migration + model + controller + FormRequest + policy + Blade index/create/edit + route + sidebar entry + permission set — so new modules are one command.
- `docs/ADDING_A_MODULE.md` documenting the pattern manually as fallback.
- `README.md` with setup, seeded super-admin creds, theming, and how to reuse the starter for a new client.

---

## Critical Files (representative)
- `composer.json`, `package.json`, `vite.config.js`, `resources/css/app.css`, `resources/js/app.js`
- `routes/web.php` (public), `routes/admin.php` (admin group)
- `app/Models/{User,Setting,Page,Post,Category,Menu,MenuItem,Media}.php`
- `app/Http/Controllers/Admin/*` + `app/Http/Requests/Admin/*`
- `app/Http/Controllers/Site/*` (public)
- `app/Providers/{AppServiceProvider,SettingsServiceProvider}.php` (bind cached settings + runtime mail/theme config)
- `database/migrations/*`, `database/seeders/*`
- `resources/views/layouts/{app,admin}.blade.php`, `resources/views/admin/**`, `resources/views/site/**`, `resources/views/components/**`
- `app/Console/Commands/MakeAdminModule.php` + `stubs/admin-module/*`

## Verification (end-to-end)
1. `composer install && npm install && npm run build` succeed with no errors.
2. `php artisan migrate:fresh --seed` creates schema + super-admin + demo content.
3. `php artisan serve` (or MAMP vhost) → visit `/` : landing renders with theme, dynamic menu, sample blog/pages.
4. `/admin/login` → log in as seeded super-admin → dashboard loads.
5. Exercise each module: create/edit/delete a user, assign a role, edit a role's permission matrix; create a Page and a Post with Jodit (insert an image → media endpoint works); build a menu; change Theme settings (gradient color) and confirm both admin + public restyle; save Mail settings and send a test email.
6. Log in as a limited `editor` role → confirm permission gates hide/deny disallowed modules.
7. Run `php artisan make:admin-module Faq` → verify a working CRUD module appears with sidebar link + permissions.
8. Mobile: verify sidebar drawer, responsive tables/forms at ~375px width.
9. `./vendor/bin/pint` clean (if added).
