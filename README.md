# Lumani Documentation Platform

Enterprise multi-tenant documentation engine (GitBook/Mintlify-style) with a high-concurrency MCP/RCP async ingestion pipeline and automated attachment processing.

## Architecture

- **Backend**: Laravel 13 / PHP 8.4 (single repository, embedded in `resources/js`)
- **Frontend**: Vue 3 + TypeScript + Vite (single SPA)
- **Database**: PostgreSQL 16 with RLS-style global scopes, declarative range partitioning, GIN JSONB/tsvector indexes
- **Queue**: Laravel Horizon on Redis with `FOR UPDATE SKIP LOCKED` concurrency
- **Cache/Rate limiting**: Redis (sliding window ZSET)
- **Search**: Meilisearch
- **Storage**: MinIO (S3-compatible)

## Multi-Tenancy

Tenant context is resolved in `TenantMiddleware` via:
1. `X-Tenant-ID` header (by ID or slug)
2. Custom domain / subdomain

Isolation is enforced via the `TenantAwareTrait` global scope applied to tenant-scoped models.

## Async Ingestion

`POST /api/v1/ingest/{project_slug}`

- Returns `202 Accepted` with a `queue_id` immediately after validation
- Idempotency via `X-Sync-Id` header + Lua-style Redis `SET NX EX` locks
- `X-API-Key` (SHA-256 hashed) machine-to-machine auth via `ApiKeyMiddleware`

## Getting Started

### Prerequisites
- Docker + Docker Compose

### Quick Start

```bash
cp backend/.env.example backend/.env
docker compose up --build -d

# Run migrations + seed
docker compose exec app php artisan migrate --seed

# Start queue worker (Horizon)
docker compose exec app php artisan horizon
```

### Frontend Dev
```bash
cd backend
npm install
npm run dev
```

## API Routes

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| POST | `/api/v1/ingest/{project_slug}` | API Key | Async payload ingestion (202 + queue_id) |
| GET | `/api/v1/ingest/{queue_id}/status` | API Key | Ingestion queue status |
| CRUD | `/api/v1/projects` | Stateful | Project management |
| CRUD | `/api/v1/documents` | API Key | Document management |
| CRUD | `/api/v1/attachments` | API Key | Attachment management |
| CRUD | `/api/v1/webhooks` | API Key | Webhook management |

## Project Structure

```
backend/
├── app/
│   ├── Console/Commands/       # Sync, attachment, AI, health commands
│   ├── Http/Controllers/       # API v1 + Web controllers
│   ├── Http/Middleware/        # Tenant, ApiKey, RateLimit
│   ├── Http/Resources/         # API resources
│   ├── Models/                 # Eloquent models + TenantAwareTrait
│   ├── Services/               # Domain service layer
│   ├── Jobs/                   # Queue workers
│   ├── Events/ & Listeners/    # Event pipeline
├── config/                     # tenant, ai, storage, mcp, horizon
├── database/migrations/        # PostgreSQL schema + partitioning
├── resources/js/               # Vue 3 SPA
└── routes/                     # web.php, api.php, console.php
docker/
├── nginx/                      # Nginx config
├── php/                        # Multi-stage Dockerfile
└── supervisor/                 # Supervisor config
```
