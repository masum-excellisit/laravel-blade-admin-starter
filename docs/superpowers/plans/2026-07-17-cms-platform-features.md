# CMS Platform Features Implementation Plan

> **For agentic workers:** Execute task-by-task. Skip i18n (explicitly excluded).

**Goal:** Add agency-ready CMS platform features (SEO, redirects, notifications, audit, revisions, blocks, forms, media upgrades, analytics, maintenance, customer auth, FAQ/Team/Portfolio, cookie consent, backup) to the Laravel Blade admin starter.

**Architecture:** Follow existing admin patterns (Spatie permissions, HandlesListQuery/BulkActions, settings cache, sidebar `$nav`, Blade + Alpine, static CSS). Prefer settings keys + thin modules over new packages unless necessary.

**Tech Stack:** Laravel 12, Blade, Spatie Permission, Intervention Image, Alpine, static `public/css`/`public/js`.

## Global Constraints

- No npm/Vite; edit static assets only
- PHP 8.2+ compatible
- Branch: `cursor/cms-platform-features-0bb7`
- Jobs table remains `job_listings`
- Inject new sidebar items before Settings marker; keep Admin Users/Roles/Permissions last
- Exclude multi-language / i18n

## Feature checklist (excludes #11 i18n)

1. SEO toolkit
2. Redirect manager
3. Email notifications (contact + job apply)
4. Activity / audit log
5. Content revisions & restore
6. Global blocks
7. Form builder
8. Media improvements
9. Analytics settings
10. Maintenance / coming soon
12. Customer frontend auth
13. FAQ / Team / Portfolio modules
14. Cookie consent banner
15. Backup / export

## Task batches

### Batch A — Foundations
- Migration(s) for new tables + SEO columns on posts
- Permissions in RolePermissionSeeder
- Shared traits: RecordsActivity, HasRevisions (optional)

### Batch B — SEO + Redirects + Maintenance + Analytics + Cookie
- SEO fields UI + sitemap/robots routes
- Redirects CRUD + middleware
- Settings: analytics scripts, maintenance mode, cookie banner copy
- Public layout hooks

### Batch C — Notifications + Audit + Revisions
- Mailables + hooks on contact/apply
- Activity log model + admin index + observer/trait
- Revisions for Page/Post/CmsContent

### Batch D — Blocks + Forms
- Global blocks CRUD + render helper
- Form builder + submissions inbox + public render

### Batch E — Media + Modules + Customer auth + Backup
- Media folders/tags/alt/replace/cleanup
- FAQ, Team, Portfolio modules + public pages
- Customer register/login/profile
- Backup export zip/json from admin

### Batch F — Tests + README + PR
- Feature tests covering main routes
- README feature list update
- Commit/push/PR
