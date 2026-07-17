# Admin Panel Enhancements Implementation Plan

> **For agentic workers:** Implement task-by-task. Steps use checkbox syntax.

**Goal:** Deliver CMS fixed-page editors, Services/Testimonials/Jobs/Job Applications modules, shared search/sort/bulk table UX, drag-orderable header/footer menus, and correct sidebar ordering.

**Architecture:** Config-driven CMS + Eloquent modules + shared list traits/components; SortableJS for menus; generator injects nav before Settings.

**Tech Stack:** Laravel 12, Blade, Alpine.js, Tailwind v4, SortableJS, Spatie Permission.

## Global Constraints
- Do not collide with Laravel queue `jobs` table — use `job_listings`.
- Keep Admin Users / Roles / Permissions after Settings.
- New modules and generator injections go before Settings.
- Follow existing Blade component / controller patterns.

---

### Task 1: Shared list infrastructure
- [ ] Add `HandlesListQuery` + `HandlesBulkActions` concerns
- [ ] Enhance `x-table`, add `x-bulk-actions`, update `x-search`
- [ ] Retrofit existing index controllers/views + stubs

### Task 2: Sidebar + generator
- [ ] Restructure sidebar with CMS group and correct order
- [ ] Move `[admin-module nav]` before Settings; update `MakeAdminModule`

### Task 3: CMS management
- [ ] `config/cms.php`, migration, model, controller, views, seeder, public helper

### Task 4: Domain modules
- [ ] Services, Testimonials, JobListings, JobApplications (+ public pages)

### Task 5: Menus drag + fixed locations
- [ ] Location select, SortableJS UI, nested render

### Task 6: Verify
- [ ] migrate:fresh --seed, npm build, feature smoke tests
