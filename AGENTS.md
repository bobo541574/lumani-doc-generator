# AGENTS.md

Enterprise multi-tenant documentation engine (Laravel 13 + Vue 3, single-repo). Blueprint: `Blueprint & Architecture.md` (source contract). This file captures only non-obvious, verified current state — trust the code over prose where they differ.

## High-signal current-state caveats

- **Standard Laravel 13 runtime skeleton is now present** (`artisan`, `public/index.php` + `.htaccess`, `bootstrap/providers.php` + `bootstrap/cache/`, `storage/` tree, and core `config/*.php` for app/auth/database/queue/filesystems/cache/logging/mail/session/broadcasting/hashing/cors/view/pagination, plus `phpunit.xml` + `tests/`, `app/Providers/AppServiceProvider.php`). Scheduler runs via `->withSchedule()` in `bootstrap/app.php` (the old `app/Console/Kernel.php` was removed). However, `vendor/` is not installed, so any `php artisan ...` / local boot will still fail until you run `composer install`. Do not assume the frameworks' defaults exist beyond what's in `config/`.
- **Not a git repo** (no `.git`). No CI, no pre-commit hooks, no lock-driven deterministic builds yet.
- `backend/composer.lock` is **absent**; `package-lock.json` exists. `docker/php/Dockerfile` expects both but tolerates the missing lock via fallbacks (`npm ci || npm install`, `composer install || composer update`). If you run composer, commit the lock for deterministic builds.
- `.gitignore` (`backend/.gitignore`) ignores `package-lock.json` while the Docker build needs it — reconcile this (unignore package-lock) if you add CI.

## Commands (verified working)

All frontend commands run from `backend/`:
- Build: `npm run build` (vite) — verified
- Type-check: `npm run type-check` (`vue-tsc --noEmit`) — verified clean; run separately, it is NOT part of build. `npm run lint` = eslint, separate again.
- `npm run dev` = vite dev server

PHP: files only pass `php -l` (syntax). No test suite exists yet — don't assume composer scripts run.

## Architecture (what the filenames don't say)

- **Multi-tenancy is bound to the request lifecycle.** `TenantAwareTrait` (global scope + `creating` hook) reads `app('tenant')`, which is ONLY set by `TenantMiddleware` (via `X-Tenant-ID` header or domain). Artisan commands, manual model creation, and jobs that create tenant-scoped models have **no tenant context** unless you bind one explicitly. This is the most common footgun.
- **Service layer = domain logic** (`app/Services/*`); controllers stay thin, `app/Jobs/*` are the queue workers (Horizon, multi-queue: default/ingestion/attachments/webhooks/ai).
- **Ingestion pipeline**: `POST /api/v1/ingest/{project_slug}` returns `202` + `queue_id`; idempotency via `X-Sync-Id` + Redis `SET NX EX` locks (`IngestController`), then `ProcessIngestJob` (transactional). API keys are SHA-256 hashed (`ApiKeyMiddleware`); rate limiting is Redis ZSET sliding window (`RateLimitMiddleware`).
- Middleware aliases registered in `bootstrap/app.php`: `tenant`, `api.key`, `api.rate`.

## Migrations quirks

- Several migrations use raw `DB::statement(...)` because the schema builder can't express: **declarative range partitioning** (`incoming_data_queue`, `activity_logs`) and the **GIN `tsvector` + trigger** on `documents` (column is added via `ALTER TABLE` after the table, in a separate statement — don't move it into the `Schema::create` closure). **Partitions are provisioned by `partitions:create`** (an Artisan command that creates monthly `<table>_<YYYY_MM>` partitions plus a `<table>_default` catch-all), invoked once by migration `2024_01_01_000014_create_partitions` and then **daily via the scheduler** (`routes/console.php`). On PostgreSQL, unique constraints on these partitioned parents must include the `created_at` partition key (they do: `PRIMARY KEY (id, created_at)` etc.).
- Migrations run in strict `timestamp_..._NNNN` filename order; the `documents` search-vector trigger references `title`/`content` columns, so keep it after those exist.
- UUID ids default via `gen_random_uuid()`, `jsonb` columns with JSON defaults, partial indexes on `attachments(processing_status)`.

## Frontend layout

- Vite entry: `resources/js/app.ts`; host layout `resources/views/app.blade.php`; router `resources/js/router/index.ts`; aliases `@`/`@components`/`@pages`/`@stores`/`@composables`/`@utils`/`@types` → `resources/js` (verbatim from `vite.config.ts` and `tsconfig.json` — keep the two in sync).
- Frontend calls `/api/v1/dashboard/stats` from the SPA.
