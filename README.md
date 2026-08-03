# Laravel Blade Admin Starter

A reusable **Laravel 12** website + custom **Blade admin panel** starter kit. Clone it to build any client site: a themeable public marketing site plus a premium, gradient, mobile-responsive admin with auth, roles & permissions, two user types (staff + customers), a rich editor, and dynamic content modules (Pages, Blog, Menus, Media, Settings) — and a one-command generator to add more.

## Stack

- **Laravel 12** · PHP 8.2+
- **Static frontend assets** (no Node/npm/Vite required)
- Tailwind-compiled CSS shipped in `public/css/app.css`
- Alpine.js, SortableJS, and Jodit vendored under `public/vendor/`
- Custom Blade auth (no Breeze) + **spatie/laravel-permission**
- **intervention/image** for media resizing
- **MySQL** (or SQLite for local)

## Features

- 🔐 Custom login, password reset, rate limiting, disabled-account guard
- 👥 Two user types from one `users` table — **Admin Users** (staff with panel access & roles) and **Users** (customers/front-end accounts, blocked from the admin panel)
- 🧑‍💼 **Customer account portal** — public register/login/profile at `/account/*`
- 🛡️ Roles (permission matrix) + Permissions — multi-admin with granular access
- 🧱 **CMS Management** for fixed pages (Home, About, Services, How It Works, Careers, Contact) with sectioned text/image fields
- 🧩 Modules: **Services**, **Testimonials**, **Jobs**, **Job Applications**, **FAQs**, **Team**, **Portfolio**
- 📝 Pages & Blog (categories, featured images, scheduling) with Jodit
- 🔎 **SEO toolkit** — meta/OG/canonical fields, `sitemap.xml`, `robots.txt`, editor checklist
- ↪️ **Redirect manager** — 301/302 rules with hit counting
- 🧱 **Global content blocks** + **Form builder** (fields + submissions inbox, public `/forms/{slug}`)
- 🕘 **Content revisions** for pages/posts/CMS + restore
- 📜 **Activity / audit log** of admin changes
- 🧭 Header/footer **Menus** with drag-and-drop item ordering
- 🔍 Admin tables: live search, click-to-sort columns, multi-select bulk actions
- 🖼️ Media library (folders, alt text, tags, replace-in-place, unused cleanup, Jodit upload, auto-resize)
- ✉️ Contact form → stored messages + **email notifications** / optional auto-reply
- 🍪 Cookie consent banner + **Analytics** snippets (GA4 / GTM / Plausible) from Settings
- 🚧 **Maintenance / coming soon** mode from Settings
- 💾 **Backup / export** of content (+ media when small)
- ⚙️ Settings: General, Theme, Mail, Analytics, Maintenance, Notifications, Cookie
- 🎨 Fully themeable via CSS variables — reskin per client without touching code
- ⚡ `php artisan make:admin-module {Name}` scaffolds a complete CRUD module (injected **before Settings**)
- 📱 Responsive: sidebar drawer, responsive tables/forms, light/dark mode

## Setup (PHP only — no npm)

```bash
# 1. Install PHP dependencies
composer install

# 2. Environment
cp .env.example .env
php artisan key:generate
# edit .env → set DB_DATABASE / DB_USERNAME / DB_PASSWORD (MySQL)

# 3. Database + demo content
php artisan migrate:fresh --seed

# 4. Storage symlink (for uploaded media/avatars)
php artisan storage:link

# 5. Serve
php artisan serve
```

Visit `http://localhost:8000` for the public site and `http://localhost:8000/admin/login` for the admin.

### Frontend assets (already in the repo)

| Path | Purpose |
|------|---------|
| `public/css/app.css` | Compiled Tailwind + theme utilities |
| `public/css/admin-dashboard.css` | Dashboard-only premium styling (loaded by the dashboard view alone) |
| `public/js/app.js` | Alpine helpers (bulk tables, menu drag, Jodit init) |
| `public/vendor/alpine/` | Alpine.js |
| `public/vendor/sortablejs/` | SortableJS |
| `public/vendor/jodit/` | Jodit editor JS/CSS |

Edit those files directly when you need UI/behavior changes. **Node and npm are not used.**

> `public/css/app.css` also contains hand-written component CSS (sidebar scroll, permission
> cards, table sort icons) that is **not** in `resources/css/app.css`. Regenerating it with a
> Tailwind CLI would drop those blocks — keep dashboard-scoped styles in
> `public/css/admin-dashboard.css` instead.

### Admin dashboard

The dashboard is assembled from independent widgets. Every block is one partial in
`resources/views/admin/dashboard/`, included from `resources/views/admin/dashboard.blade.php`.

| Widget | Partial | Data |
|--------|---------|------|
| KPI cards + sparklines | `_kpis` | `$kpis` |
| Quick actions | `_quick-actions` | self-contained array |
| Growth chart (30d / 12m) | `_trend` | `$trend` |
| Content mix donut | `_content-mix` | `$contentMix` |
| Inbound bar chart | `_engagement` | `$engagement` |
| Publishing gauge + env | `_system` | `$system` |
| Recent posts / customers | `_recent-posts`, `_recent-users` | `$recentPosts`, `$recentUsers` |
| Activity timeline | `_activity` | `$recentActivity` |

**Removing things**

- One widget → delete its `@include` line (and optionally the partial + its method in `app/Services/DashboardAnalytics.php`).
- All premium styling → drop the `@push('styles')` block and `public/css/admin-dashboard.css`.
- All charts → delete `resources/views/components/chart/` and the widgets that use them.
- All analytics → delete `app/Services/DashboardAnalytics.php` and simplify `DashboardController`.

Charts are inline SVG rendered from PHP with Alpine for hover state — no chart library,
no build step. Aggregates are cached for `DashboardAnalytics::CACHE_TTL` seconds (set it to
`0` to disable, or call `DashboardAnalytics::flush()` after writes).

### Seeded logins

**Admin Users** (staff — can sign in at `/admin/login`):

| Role | Email | Password |
|------|-------|----------|
| Super admin | `superadmin@yopmail.com` | `12345678` |
| Admin | `main@yopmail.com` | `12345678` |
| Editor | `editor@yopmail.com` | `12345678` |

`super-admin` bypasses all permission checks (via `Gate::before`). `admin` gets everything except managing permissions. The `editor` role can only manage content (pages, posts, categories, media, messages).

**Users** (customers — seeded as demo records, **cannot** access the admin panel): `alice@example.com`, `bruno@example.com`, `chen@example.com` — password `password`.

## User types

Every account lives in the `users` table with a `type` column: `admin` or `customer` (default `admin`).

- **Admin Users** (`type = admin`) — managed under **Admin Users**; assigned roles/permissions; the only accounts allowed to log in at `/admin`.
- **Users** (`type = customer`) — managed under **Users**; no roles; rejected by the admin login.

The `User` model exposes `admins()` / `customers()` query scopes and `isAdmin()` / `isCustomer()` helpers. The admin login guard lives in `App\Http\Controllers\Admin\Auth\LoginController`.

> Note: in the sidebar and dashboard, the customer module is labelled **Users** and the staff module **Admin Users**. The underlying routes are `/admin/customers` (Users) and `/admin/users` (Admin Users).

## Theming

Go to **Admin → Settings → Theme** and change the primary/secondary/accent/sidebar colours. They're stored in the DB and rendered as CSS variables in `resources/views/partials/theme.blade.php`, consumed by both the admin panel and the public site — so a client rebrand is a few colour pickers, no code.

To change fonts or base tokens, edit `public/css/app.css`.

## Mail

SMTP is configured from the DB (**Settings → Mail**) and applied at runtime in `App\Providers\SettingsServiceProvider`. Use **Send test email** to verify. If no host is set, Laravel falls back to the `.env` mailer (default `log`).

## Adding modules

```bash
php artisan make:admin-module Faq
```

See [docs/ADDING_A_MODULE.md](docs/ADDING_A_MODULE.md) for the full pattern (generator and manual).

## Project structure

```
app/Http/Controllers/Admin/*   Admin controllers
app/Http/Controllers/Site/*    Public controllers
app/Http/Requests/Admin/*      Form requests
app/Models/*                   Eloquent models
app/Providers/SettingsServiceProvider.php   Cached settings, runtime mail/theme, super-admin gate
routes/web.php                 Public routes
routes/admin.php               Admin routes (prefix /admin, name admin.)
resources/views/admin/**       Admin views
resources/views/site/**        Public views
resources/views/components/**  Reusable Blade components (x-btn, x-card, x-table, x-form.*, …)
public/css, public/js, public/vendor   Static frontend (no npm)
database/seeders/*             Roles, admin user, settings, demo content
stubs/admin-module/*           Templates used by make:admin-module
```

## License

MIT.
