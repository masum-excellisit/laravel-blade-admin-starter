# Admin Panel Enhancements — Design

## Goal
Upgrade the Blade admin starter with sectioned CMS for fixed pages, domain modules (Services, Testimonials, Jobs, Job Applications), consistent list UX (search / column sort / multi-select bulk actions), fixed header/footer menu management with drag-ordering, and sidebar ordering rules that keep Admin Users / Roles / Permissions below Settings while placing content modules before Settings.

## Architecture

### 1. CMS Management (fixed pages)
- Config-driven schemas in `config/cms.php` define fixed pages (home, about, services, how-it-works, careers, contact) with named sections and typed fields (`text`, `textarea`, `richtext`, `image`, `url`).
- Persistence: `cms_contents` table (`page`, `section`, `data` JSON). One row per page+section.
- Admin: collapsible **CMS Management** sidebar group linking to per-page editors that render section cards with the configured inputs (including image upload via existing media disk).
- Public site reads CMS content via a `Cms` helper / facade and falls back to sensible defaults when empty.

### 2. Domain modules
- **Services** (`services`): title, slug, excerpt, body, icon/image, sort_order, status.
- **Testimonials** (`testimonials`): author_name, author_title, quote, avatar, rating, sort_order, status.
- **Jobs** (`job_listings` table — avoids Laravel queue `jobs` table collision): title, slug, location, employment_type, description, requirements, status, published_at. Admin route/label: Jobs.
- **Job Applications** (`job_applications`): job_listing_id, name, email, phone, resume_path, cover_letter, status (new/reviewed/shortlisted/rejected/hired). Read-heavy admin (view + status update + delete); public apply form on job detail.

### 3. Sidebar order
```
Dashboard → CMS Management (group) → Users → Services → Testimonials → Jobs → Job Applications
→ Pages → Posts → Categories → Menus → Media → Messages → [generated modules] → Settings
→ Admin Users → Roles → Permissions
```
- `make:admin-module` injects at `// [admin-module nav]` placed **immediately before Settings**.
- Admin Users / Roles / Permissions stay hard-coded after Settings.

### 4. Shared list UX
- Trait `HandlesListQuery` + `HandlesBulkActions` used by all index controllers.
- Enhanced `<x-table>`: sortable column headers (toggle `sort` / `direction` query params), optional checkbox column, Alpine select-all.
- `<x-bulk-actions>` toolbar: delete / publish / draft (module-appropriate).
- `<x-search>` already exists — retrofit onto every index + generator stubs.

### 5. Menus
- Location constrained to `header` | `footer` (select, not free text). Seeded fixed menus remain.
- Menu edit UI: SortableJS drag handles calling existing `menus.reorder` endpoint; cleaner item cards; optional parent for nesting.
- Public menu component renders nested children.

## Out of scope
- Full page-builder WYSIWYG drag canvas.
- Nested CMS field repeaters beyond configured sections.
- Changing public marketing visual design system (only wire CMS data into existing layouts).
