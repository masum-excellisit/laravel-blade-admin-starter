# Laravel Blade Admin Starter

A reusable **Laravel 12** website + custom **Blade admin panel** starter kit. Clone it to build any client site: a themeable public marketing site plus a premium, gradient, mobile-responsive admin with auth, roles & permissions, a rich editor, and dynamic content modules (Pages, Blog, Menus, Media, Settings) — and a one-command generator to add more.

## Stack

- **Laravel 12** · PHP 8.2+
- **Tailwind CSS v4** + **Alpine.js**, bundled via **Vite**
- Custom Blade auth (no Breeze) + **spatie/laravel-permission**
- **Jodit** rich text editor
- **intervention/image** for media resizing
- **MySQL**

## Features

- 🔐 Custom login, password reset, rate limiting, disabled-account guard
- 👥 Users, Roles (permission matrix), Permissions — multi-admin with granular access
- 📝 Pages & Blog (categories, featured images, scheduling) with Jodit
- 🧭 Dynamic header/footer Menus builder
- 🖼️ Media library (+ Jodit upload endpoint, auto-resize)
- ✉️ Contact form → stored messages
- ⚙️ Settings: General, **Mail** (DB-driven SMTP + test email), **Theme** (gradient colours drive both admin & public site)
- 🎨 Fully themeable via CSS variables — reskin per client without touching code
- ⚡ `php artisan make:admin-module {Name}` scaffolds a complete CRUD module
- 📱 Responsive: sidebar drawer, responsive tables/forms, light/dark mode

## Setup

```bash
# 1. Install dependencies
composer install
npm install

# 2. Environment
cp .env.example .env
php artisan key:generate
# edit .env → set DB_DATABASE / DB_USERNAME / DB_PASSWORD (MySQL)

# 3. Database + demo content
php artisan migrate:fresh --seed

# 4. Storage symlink (for uploaded media/avatars)
php artisan storage:link

# 5. Build assets
npm run build      # or: npm run dev

# 6. Serve
php artisan serve
```

Visit `http://localhost:8000` for the public site and `http://localhost:8000/admin/login` for the admin.

### Seeded logins

| Role | Email | Password |
|------|-------|----------|
| Super admin | `admin@example.com` | `password` |
| Editor | `editor@example.com` | `password` |

`super-admin` bypasses all permission checks (via `Gate::before`). The `editor` role can only manage content (pages, posts, categories, media, messages).

## Theming

Go to **Admin → Settings → Theme** and change the primary/secondary/accent/sidebar colours. They're stored in the DB and rendered as CSS variables in `resources/views/partials/theme.blade.php`, consumed by both the admin panel and the public site — so a client rebrand is a few colour pickers, no code.

To change fonts or base tokens, edit `resources/css/app.css`.

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
database/seeders/*             Roles, admin user, settings, demo content
stubs/admin-module/*           Templates used by make:admin-module
```

## License

MIT.
