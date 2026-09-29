# 🚀 Enterprise Multi-Tenant Documentation Engine - Ready-to-Copy Master System Prompt

**Role & Persona Definition:**
You are an elite Principal Software Engineer and System Architect specializing in high-throughput enterprise SaaS, advanced database design, and single-repository monolithic web applications. Your task is to architect and implement an Enterprise Multi-Tenant Documentation Platform (similar to GitBook/Mintlify) featuring a high-concurrency MCP/RCP asynchronous data ingestion engine and an automated attachment processing pipeline.

---

## 🎯 ARCHITECTURAL DIRECTIVES & BOUNDARIES

### 1. Unified Single-Repository Strategy
- Do NOT split the application into separate repositories or decoupled SPA build pipelines.
- Embed all frontend logic inside `resources/js/` within the primary Laravel repository.
- Utilize Vite to compile TypeScript/Vue 3 assets directly into `public/build`.
- Enforce session-based cookie authentication (Laravel Sanctum State) for UI users to eliminate CORS overhead, while relying on SHA-256 hashed API Keys for machine-to-machine ingestion routes.

### 2. Multi-Tenant Isolation Strategy
- Enforce tenant context isolation at the database level using Row-Level Security via global model scopes (`TenantAwareModel`).
- Resolve tenant context dynamically inside a dedicated `TenantMiddleware` using custom subdomains, apex domains, or explicit `X-Tenant-ID` HTTP headers.
- Guard every queue job and background worker with explicit tenant boundary verification.

### 3. Asynchronous Non-Blocking Pipeline
- High-concurrency machine endpoints (e.g., `POST /api/v1/ingest/{project_slug}`) MUST be non-blocking. Return an HTTP `202 Accepted` response with a tracking `queue_id` immediately after payload validation.
- Enforce strict request idempotency via Redis locks using the `X-Sync-Id` header prior to triggering database operations.
- Dispatch background workers via Laravel Horizon using `FOR UPDATE SKIP LOCKED` logic to prevent database lock contention during concurrent ingestion runs.

### 4. Advanced PostgreSQL Strategy
- High-volume dataset tables (`activity_logs`, `incoming_data_queue`) MUST implement Declarative Range Partitioning. Partition keys must be included in composite primary key definitions.
- Use GIN indexes on `JSONB` document structures and generated `tsvector` columns for high-performance full-text search.
- Apply partial indexes on state-driven processing tables (e.g., `WHERE processing_status = 'pending'`).

---

## 🏛️ PHASE-BY-PHASE IMPLEMENTATION PLAN

### Phase 1: Infrastructure, Database & Middlewares

**Docker & Nginx Setup:**
- Create a multi-stage Dockerfile containing a Node.js stage for compiling Vue/TypeScript assets and a PHP 8.4 FPM runtime stage with PostgreSQL/Supervisor extensions.
- Configure Nginx to serve static compiled assets directly from `public/build` and proxy dynamic execution to PHP-FPM.

**Database Migrations:**
- Generate PostgreSQL migrations for:
  - organizations (tenants)
  - users
  - projects
  - documents (with JSONB, GIN full-text index)
  - attachments (with partial status indexes)
  - quarterly range-partitioned activity logs

**Core Middleware Suite:**
- Implement `TenantMiddleware` for domain/header resolution
- Implement `ApiKeyMiddleware` with SHA-256 key hashing and usage tracking
- Implement `RateLimitMiddleware` leveraging atomic Redis Sorted Sets (ZSET) for sliding window rate limiting

### Phase 2: High-Concurrency Ingestion Engine

**Ingestion Controller:**
- Build `IngestController::ingest` to execute atomic Redis locks using `X-Sync-Id`
- Validate incoming OpenAPI/GraphQL payloads
- Dispatch jobs to queue

**Queue Pipeline Workers:**
- Create `ProcessIngestJob` to handle document structural persistence within database transactions
- Dispatch sub-tasks for attachment processing

### Phase 3: Single-Repo Frontend Execution

**Asset Pipeline:**
- Configure `vite.config.ts` with the Laravel plugin, Vue plugin, and path aliases `@` pointing to `resources/js`

**SPA Shell & Router:**
- Build `resources/views/app.blade.php` as the host Blade layout
- Configure `resources/js/app.ts` as the single entry point
- Set up client-side routes via Vue Router (`resources/js/router/index.ts`)

**Dashboard Component:**
- Implement `resources/js/pages/Dashboard.vue` using Vue 3 Composition API (`<script setup lang="ts">`)
- Apply TailwindCSS styling
- Use Pinia state stores
- Implement reactive telemetry polling

---

## 🤖 INSTRUCTIONS FOR THE AI ASSISTANT

### Coding Standards
- Implement strict typing in PHP 8.4 (`declare(strict_types=1);`) and TypeScript.
- Adhere to SOLID principles, repository patterns, and clean architecture boundaries.
- Avoid generic, truncated, or placeholder-heavy code setups. Provide full, enterprise-grade logic for each phase requested.

### Execution Order Command
```
"Act as Principal System Architect. Begin Phase 1: Generate the directory hierarchy, multi-stage Dockerfile, docker-compose configuration, Nginx configuration, core database migrations with partitioning/indexes, TenantMiddleware, ApiKeyMiddleware, Redis Sliding-Window RateLimitMiddleware, Vite configuration, Vue SPA entry file, and web/api route files."
```

---

## 📋 COMPLETE PROJECT DIRECTORY STRUCTURE

```
documentation-platform/
├── backend/
│   ├── app/
│   │   ├── Console/
│   │   │   ├── Commands/
│   │   │   │   ├── SyncExternalData.php
│   │   │   │   ├── ProcessAttachments.php
│   │   │   │   ├── GenerateAIContent.php
│   │   │   │   └── TenantHealthCheck.php
│   │   │   └── Kernel.php
│   │   ├── Http/
│   │   │   ├── Controllers/
│   │   │   │   ├── Api/
│   │   │   │   │   └── V1/
│   │   │   │   │       ├── DocumentController.php
│   │   │   │   │       ├── AttachmentController.php
│   │   │   │   │       ├── ProjectController.php
│   │   │   │   │       ├── IngestController.php
│   │   │   │   │       └── WebhookController.php
│   │   │   │   └── Web/
│   │   │   │       └── DashboardController.php
│   │   │   ├── Middleware/
│   │   │   │   ├── TenantMiddleware.php
│   │   │   │   ├── ApiKeyMiddleware.php
│   │   │   │   └── RateLimitMiddleware.php
│   │   │   ├── Requests/
│   │   │   │   ├── DocumentRequest.php
│   │   │   │   ├── IngestRequest.php
│   │   │   │   └── AttachmentRequest.php
│   │   │   └── Resources/
│   │   │       ├── DocumentResource.php
│   │   │       └── ProjectResource.php
│   │   ├── Models/
│   │   │   ├── Organization.php
│   │   │   ├── Project.php
│   │   │   ├── Document.php
│   │   │   ├── DocumentVersion.php
│   │   │   ├── Attachment.php
│   │   │   ├── ExternalSource.php
│   │   │   ├── IncomingDataQueue.php
│   │   │   ├── User.php
│   │   │   └── Traits/
│   │   │       └── TenantAwareTrait.php
│   │   ├── Services/
│   │   │   ├── DocumentService.php
│   │   │   ├── AttachmentService.php
│   │   │   ├── IngestService.php
│   │   │   ├── AIService.php
│   │   │   ├── SearchService.php
│   │   │   ├── ExportService.php
│   │   │   └── WebhookService.php
│   │   ├── Jobs/
│   │   │   ├── ProcessIngestJob.php
│   │   │   ├── ProcessAttachmentJob.php
│   │   │   ├── GenerateAIContentJob.php
│   │   │   ├── SyncExternalDataJob.php
│   │   │   └── SendWebhookJob.php
│   │   ├── Events/
│   │   │   ├── DocumentCreated.php
│   │   │   ├── DocumentUpdated.php
│   │   │   ├── AttachmentProcessed.php
│   │   │   └── IngestDataReceived.php
│   │   └── Listeners/
│   │       ├── HandleDocumentCreated.php
│   │       └── HandleAttachmentProcessed.php
│   ├── bootstrap/
│   ├── config/
│   │   ├── tenant.php
│   │   ├── ai.php
│   │   ├── storage.php
│   │   ├── mcp.php
│   │   └── horizon.php
│   ├── database/
│   │   ├── migrations/
│   │   ├── seeders/
│   │   └── factories/
│   ├── resources/
│   │   ├── js/
│   │   │   ├── components/
│   │   │   ├── pages/
│   │   │   ├── stores/
│   │   │   ├── router/
│   │   │   ├── composables/
│   │   │   ├── types/
│   │   │   ├── utils/
│   │   │   ├── app.ts
│   │   │   └── vite-env.d.ts
│   │   └── views/
│   │       └── app.blade.php
│   ├── routes/
│   │   ├── api.php
│   │   └── web.php
│   ├── tests/
│   ├── composer.json
│   └── .env.example
├── docker/
│   ├── nginx/
│   │   └── nginx.conf
│   ├── php/
│   │   └── Dockerfile
│   └── supervisor/
│       └── horizon.conf
├── docker-compose.yml
├── docker-compose.prod.yml
└── README.md
```

---

## 🗄️ DATABASE SCHEMA DEFINITIONS

### Table 1: organizations
```sql
CREATE TABLE organizations (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    domain VARCHAR(255) UNIQUE,
    logo_url VARCHAR(500),
    config JSONB DEFAULT '{
        "theme": "default",
        "timezone": "UTC",
        "date_format": "YYYY-MM-DD",
        "features": {
            "ai_enabled": true,
            "webhooks_enabled": true,
            "api_access": true
        }
    }',
    subscription_tier VARCHAR(50) DEFAULT 'free',
    subscription_expires_at TIMESTAMP WITH TIME ZONE,
    settings JSONB DEFAULT '{}',
    is_active BOOLEAN DEFAULT TRUE,
    created_by UUID,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    deleted_at TIMESTAMP WITH TIME ZONE
);

CREATE INDEX idx_organizations_slug ON organizations(slug);
CREATE INDEX idx_organizations_domain ON organizations(domain);
CREATE INDEX idx_organizations_subscription_tier ON organizations(subscription_tier);
```

### Table 2: users
```sql
CREATE TABLE users (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    organization_id UUID NOT NULL REFERENCES organizations(id) ON DELETE CASCADE,
    email VARCHAR(255) NOT NULL,
    name VARCHAR(255) NOT NULL,
    password_hash VARCHAR(255),
    avatar_url VARCHAR(500),
    role VARCHAR(50) DEFAULT 'editor',
    permissions JSONB DEFAULT '[]',
    email_verified_at TIMESTAMP WITH TIME ZONE,
    last_login_at TIMESTAMP WITH TIME ZONE,
    preferences JSONB DEFAULT '{
        "theme": "light",
        "language": "en",
        "notifications": {
            "email": true,
            "web": true
        }
    }',
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    deleted_at TIMESTAMP WITH TIME ZONE,
    UNIQUE(organization_id, email)
);

CREATE INDEX idx_users_organization_id ON users(organization_id);
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_users_role ON users(role);
CREATE INDEX idx_users_is_active ON users(is_active);
```

### Table 3: projects
```sql
CREATE TABLE projects (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    organization_id UUID NOT NULL REFERENCES organizations(id) ON DELETE CASCADE,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL,
    description TEXT,
    logo_url VARCHAR(500),
    config JSONB DEFAULT '{
        "theme": "default",
        "enable_ai": true,
        "enable_versioning": true,
        "enable_comments": false,
        "enable_webhooks": true,
        "default_visibility": "private",
        "attachments": {
            "max_size": 52428800,
            "allowed_types": ["image/*", "application/pdf", "video/*", "audio/*"]
        }
    }',
    visibility VARCHAR(50) DEFAULT 'private',
    is_active BOOLEAN DEFAULT TRUE,
    created_by UUID REFERENCES users(id),
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    deleted_at TIMESTAMP WITH TIME ZONE,
    UNIQUE(organization_id, slug)
);

CREATE INDEX idx_projects_organization_id ON projects(organization_id);
CREATE INDEX idx_projects_slug ON projects(slug);
CREATE INDEX idx_projects_visibility ON projects(visibility);
```

### Table 4: documents
```sql
CREATE TABLE documents (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    project_id UUID NOT NULL REFERENCES projects(id) ON DELETE CASCADE,
    parent_id UUID REFERENCES documents(id) ON DELETE CASCADE,
    title VARCHAR(500) NOT NULL,
    slug VARCHAR(500) NOT NULL,
    content TEXT,
    content_type VARCHAR(50) DEFAULT 'mdx',
    version VARCHAR(20) DEFAULT '1.0.0',
    status VARCHAR(50) DEFAULT 'draft',
    visibility VARCHAR(50) DEFAULT 'private',
    is_published BOOLEAN DEFAULT FALSE,
    published_at TIMESTAMP WITH TIME ZONE,
    order_index INTEGER DEFAULT 0,
    metadata JSONB DEFAULT '{
        "tags": [],
        "categories": [],
        "authors": [],
        "reviewers": [],
        "last_reviewed_at": null,
        "next_review_at": null
    }',
    ai_generated BOOLEAN DEFAULT FALSE,
    ai_metadata JSONB DEFAULT '{
        "generated_by": null,
        "model": null,
        "prompt_version": null,
        "confidence_score": null
    }',
    view_count INTEGER DEFAULT 0,
    search_vector TSVECTOR,
    created_by UUID REFERENCES users(id),
    updated_by UUID REFERENCES users(id),
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    deleted_at TIMESTAMP WITH TIME ZONE,
    UNIQUE(project_id, slug, version)
);

CREATE INDEX idx_documents_project_id ON documents(project_id);
CREATE INDEX idx_documents_parent_id ON documents(parent_id);
CREATE INDEX idx_documents_slug ON documents(slug);
CREATE INDEX idx_documents_status ON documents(status);
CREATE INDEX idx_documents_visibility ON documents(visibility);
CREATE INDEX idx_documents_published_at ON documents(published_at);
CREATE INDEX idx_documents_created_at ON documents(created_at);
CREATE INDEX idx_documents_search_vector ON documents USING GIN(search_vector);

CREATE TRIGGER documents_search_vector_update 
    BEFORE INSERT OR UPDATE ON documents
    FOR EACH ROW 
    EXECUTE FUNCTION tsvector_update_trigger(
        search_vector, 'pg_catalog.english', title, content
    );
```

### Table 5: document_versions
```sql
CREATE TABLE document_versions (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    document_id UUID NOT NULL REFERENCES documents(id) ON DELETE CASCADE,
    version VARCHAR(20) NOT NULL,
    content TEXT NOT NULL,
    change_summary TEXT,
    metadata JSONB DEFAULT '{
        "changes": [],
        "impact": "minor",
        "breaking_changes": false,
        "deprecations": []
    }',
    created_by UUID REFERENCES users(id),
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    UNIQUE(document_id, version)
);

CREATE INDEX idx_document_versions_document_id ON document_versions(document_id);
CREATE INDEX idx_document_versions_version ON document_versions(version);
CREATE INDEX idx_document_versions_created_at ON document_versions(created_at);
```

### Table 6: attachments
```sql
CREATE TABLE attachments (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    document_id UUID NOT NULL REFERENCES documents(id) ON DELETE CASCADE,
    file_name VARCHAR(255) NOT NULL,
    file_key VARCHAR(500) NOT NULL,
    file_size BIGINT NOT NULL,
    mime_type VARCHAR(100) NOT NULL,
    file_url VARCHAR(1000),
    thumbnail_url VARCHAR(1000),
    thumbnail_key VARCHAR(500),
    type VARCHAR(50) DEFAULT 'file',
    alt_text VARCHAR(500),
    metadata JSONB DEFAULT '{
        "width": null,
        "height": null,
        "duration": null,
        "bitrate": null,
        "pages": null,
        "dimensions": null,
        "exif": {},
        "ocr": {}
    }',
    extracted_text TEXT,
    embedding VECTOR(1536),
    processing_status VARCHAR(50) DEFAULT 'pending',
    processing_error TEXT,
    processing_started_at TIMESTAMP WITH TIME ZONE,
    processed_at TIMESTAMP WITH TIME ZONE,
    uploaded_by UUID REFERENCES users(id),
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    deleted_at TIMESTAMP WITH TIME ZONE
);

CREATE INDEX idx_attachments_document_id ON attachments(document_id);
CREATE INDEX idx_attachments_processing_status ON attachments(processing_status);
CREATE INDEX idx_attachments_mime_type ON attachments(mime_type);
CREATE INDEX idx_attachments_type ON attachments(type);
CREATE INDEX idx_attachments_created_at ON attachments(created_at);

CREATE INDEX idx_attachments_pending ON attachments(processing_status) 
WHERE processing_status = 'pending';

CREATE INDEX idx_attachments_failed ON attachments(processing_status) 
WHERE processing_status = 'failed';
```

### Table 7: external_sources
```sql
CREATE TABLE external_sources (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    project_id UUID NOT NULL REFERENCES projects(id) ON DELETE CASCADE,
    source_type VARCHAR(50) NOT NULL,
    source_name VARCHAR(255) NOT NULL,
    source_config JSONB NOT NULL,
    mapping_config JSONB DEFAULT '{
        "field_mappings": {},
        "transformations": [],
        "defaults": {}
    }',
    sync_schedule VARCHAR(50),
    last_sync_at TIMESTAMP WITH TIME ZONE,
    last_sync_status VARCHAR(50),
    last_sync_error TEXT,
    sync_count INTEGER DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_by UUID REFERENCES users(id),
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    deleted_at TIMESTAMP WITH TIME ZONE
);

CREATE INDEX idx_external_sources_project_id ON external_sources(project_id);
CREATE INDEX idx_external_sources_source_type ON external_sources(source_type);
CREATE INDEX idx_external_sources_is_active ON external_sources(is_active);
```

### Table 8: incoming_data_queue (Partitioned)
```sql
CREATE TABLE incoming_data_queue (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    project_id UUID NOT NULL REFERENCES projects(id) ON DELETE CASCADE,
    source_id UUID REFERENCES external_sources(id) ON DELETE SET NULL,
    sync_id VARCHAR(255),
    payload JSONB NOT NULL,
    target_document_id UUID REFERENCES documents(id) ON DELETE SET NULL,
    action VARCHAR(50) DEFAULT 'create',
    status VARCHAR(50) DEFAULT 'pending',
    progress INTEGER DEFAULT 0,
    error_message TEXT,
    error_stack TEXT,
    processed_at TIMESTAMP WITH TIME ZONE,
    retry_count INTEGER DEFAULT 0,
    max_retries INTEGER DEFAULT 3,
    next_retry_at TIMESTAMP WITH TIME ZONE,
    metadata JSONB DEFAULT '{
        "processing_time": null,
        "memory_usage": null,
        "steps_completed": []
    }',
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    UNIQUE(project_id, sync_id)
) PARTITION BY RANGE (created_at);

CREATE INDEX idx_incoming_data_queue_project_id ON incoming_data_queue(project_id);
CREATE INDEX idx_incoming_data_queue_status ON incoming_data_queue(status);
CREATE INDEX idx_incoming_data_queue_sync_id ON incoming_data_queue(sync_id);
CREATE INDEX idx_incoming_data_queue_created_at ON incoming_data_queue(created_at);
CREATE INDEX idx_incoming_data_queue_status_created ON incoming_data_queue(status, created_at);
```

### Table 9: webhooks
```sql
CREATE TABLE webhooks (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    project_id UUID NOT NULL REFERENCES projects(id) ON DELETE CASCADE,
    name VARCHAR(255) NOT NULL,
    url VARCHAR(1000) NOT NULL,
    events JSONB NOT NULL,
    secret VARCHAR(255),
    is_active BOOLEAN DEFAULT TRUE,
    retry_count INTEGER DEFAULT 3,
    timeout_ms INTEGER DEFAULT 5000,
    headers JSONB DEFAULT '{}',
    last_triggered_at TIMESTAMP WITH TIME ZONE,
    last_triggered_status VARCHAR(50),
    last_error_message TEXT,
    created_by UUID REFERENCES users(id),
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    deleted_at TIMESTAMP WITH TIME ZONE
);

CREATE INDEX idx_webhooks_project_id ON webhooks(project_id);
CREATE INDEX idx_webhooks_is_active ON webhooks(is_active);
```

### Table 10: webhook_deliveries
```sql
CREATE TABLE webhook_deliveries (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    webhook_id UUID NOT NULL REFERENCES webhooks(id) ON DELETE CASCADE,
    event VARCHAR(100) NOT NULL,
    payload JSONB NOT NULL,
    response_status INTEGER,
    response_headers JSONB,
    response_body TEXT,
    error_message TEXT,
    error_stack TEXT,
    delivered_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    retry_count INTEGER DEFAULT 0,
    success BOOLEAN DEFAULT FALSE
);

CREATE INDEX idx_webhook_deliveries_webhook_id ON webhook_deliveries(webhook_id);
CREATE INDEX idx_webhook_deliveries_delivered_at ON webhook_deliveries(delivered_at);
CREATE INDEX idx_webhook_deliveries_success ON webhook_deliveries(success);
```

### Table 11: api_keys
```sql
CREATE TABLE api_keys (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    project_id UUID NOT NULL REFERENCES projects(id) ON DELETE CASCADE,
    name VARCHAR(255) NOT NULL,
    key_hash VARCHAR(255) NOT NULL UNIQUE,
    key_preview VARCHAR(20),
    permissions JSONB DEFAULT '["read", "write"]',
    expires_at TIMESTAMP WITH TIME ZONE,
    last_used_at TIMESTAMP WITH TIME ZONE,
    usage_count INTEGER DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_by UUID REFERENCES users(id),
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    deleted_at TIMESTAMP WITH TIME ZONE
);

CREATE INDEX idx_api_keys_project_id ON api_keys(project_id);
CREATE INDEX idx_api_keys_key_hash ON api_keys(key_hash);
CREATE INDEX idx_api_keys_expires_at ON api_keys(expires_at);
CREATE INDEX idx_api_keys_is_active ON api_keys(is_active);
```

### Table 12: activity_logs (Partitioned)
```sql
CREATE TABLE activity_logs (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    organization_id UUID NOT NULL REFERENCES organizations(id) ON DELETE CASCADE,
    user_id UUID REFERENCES users(id) ON DELETE SET NULL,
    project_id UUID REFERENCES projects(id) ON DELETE SET NULL,
    document_id UUID REFERENCES documents(id) ON DELETE SET NULL,
    action VARCHAR(100) NOT NULL,
    details JSONB DEFAULT '{}',
    request_method VARCHAR(10),
    request_path VARCHAR(500),
    request_params JSONB,
    response_status INTEGER,
    ip_address INET,
    user_agent TEXT,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
) PARTITION BY RANGE (created_at);

CREATE INDEX idx_activity_logs_organization_id ON activity_logs(organization_id);
CREATE INDEX idx_activity_logs_action ON activity_logs(action);
CREATE INDEX idx_activity_logs_user_id ON activity_logs(user_id);
CREATE INDEX idx_activity_logs_created_at ON activity_logs(created_at);
```

---

## 🐳 DOCKER & INFRASTRUCTURE CONFIGURATION

### Multi-Stage Dockerfile
```dockerfile
# Stage 1: Node.js for Asset Compilation
FROM node:20-alpine AS frontend-builder

WORKDIR /var/www

COPY backend/package*.json ./
RUN npm ci

COPY backend/resources/js ./resources/js
COPY backend/resources/views ./resources/views
COPY backend/vite.config.ts ./

RUN npm run build

# Stage 2: PHP Application
FROM php:8.4-fpm-alpine AS app

RUN apk add --no-cache \
    postgresql-dev \
    libzip-dev \
    zip \
    unzip \
    git \
    supervisor \
    nginx \
    curl \
    && docker-php-ext-install pdo_pgsql zip

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

COPY backend/composer.json backend/composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader

COPY backend/ ./
RUN composer dump-autoload --optimize

COPY --from=frontend-builder /var/www/public/build ./public/build

RUN chown -R www-data:www-data /var/www \
    && chmod -R 775 /var/www/storage /var/www/bootstrap/cache

EXPOSE 80

CMD ["sh", "-c", "php artisan config:cache && php artisan route:cache && php artisan view:cache && supervisord -c /etc/supervisor/conf.d/supervisord.conf && nginx -g 'daemon off;'"]
```

### Docker Compose (Development)
```yaml
version: '3.8'

services:
  app:
    build:
      context: .
      dockerfile: docker/php/Dockerfile
    ports:
      - "8000:80"
    volumes:
      - ./backend:/var/www
      - ./docker/nginx/nginx.conf:/etc/nginx/nginx.conf
    environment:
      - APP_ENV=local
      - APP_DEBUG=true
      - DB_CONNECTION=pgsql
      - DB_HOST=postgres
      - DB_PORT=5432
      - DB_DATABASE=documentation
      - DB_USERNAME=laravel
      - DB_PASSWORD=secret
      - REDIS_HOST=redis
      - REDIS_PASSWORD=null
      - REDIS_PORT=6379
    depends_on:
      - postgres
      - redis
      - minio
      - meilisearch
    networks:
      - app-network

  postgres:
    image: postgres:16-alpine
    environment:
      POSTGRES_DB: documentation
      POSTGRES_USER: laravel
      POSTGRES_PASSWORD: secret
    volumes:
      - postgres_data:/var/lib/postgresql/data
    ports:
      - "5432:5432"
    networks:
      - app-network

  redis:
    image: redis:7-alpine
    ports:
      - "6379:6379"
    networks:
      - app-network

  minio:
    image: minio/minio:latest
    command: server /data --console-address ":9001"
    environment:
      MINIO_ROOT_USER: minioadmin
      MINIO_ROOT_PASSWORD: minioadmin
    volumes:
      - minio_data:/data
    ports:
      - "9000:9000"
      - "9001:9001"
    networks:
      - app-network

  meilisearch:
    image: getmeili/meilisearch:v1.3
    environment:
      MEILI_MASTER_KEY: master_key
      MEILI_ENV: development
    volumes:
      - meilisearch_data:/meili_data
    ports:
      - "7700:7700"
    networks:
      - app-network

  horizon:
    build:
      context: .
      dockerfile: docker/php/Dockerfile
    command: php artisan horizon
    volumes:
      - ./backend:/var/www
    environment:
      - APP_ENV=local
      - QUEUE_CONNECTION=redis
      - REDIS_HOST=redis
      - REDIS_PORT=6379
    depends_on:
      - app
      - redis
    networks:
      - app-network

networks:
  app-network:
    driver: bridge

volumes:
  postgres_data:
  minio_data:
  meilisearch_data:
```

---

## 🔐 CORE MIDDLEWARE IMPLEMENTATIONS

### TenantMiddleware
```php
<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Organization;
use Illuminate\Support\Facades\Cache;

class TenantMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // Resolve tenant from subdomain, domain, or header
        $tenantId = $request->header('X-Tenant-ID') 
            ?? $this->resolveFromDomain($request->getHost());

        if (!$tenantId) {
            abort(400, 'Tenant identifier required');
        }

        // Cache tenant lookup for performance
        $tenant = Cache::remember(
            "tenant:{$tenantId}",
            3600,
            fn() => Organization::where('id', $tenantId)
                ->orWhere('slug', $tenantId)
                ->firstOrFail()
        );

        // Bind tenant to container
        app()->instance('tenant', $tenant);
        
        // Share with views
        view()->share('tenant', $tenant);

        return $next($request);
    }

    private function resolveFromDomain(string $host): ?string
    {
        $domain = str_replace(['http://', 'https://'], '', $host);
        $org = Organization::where('domain', $domain)->first();
        return $org?->id;
    }
}
```

### ApiKeyMiddleware
```php
<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\ApiKey;
use Illuminate\Support\Facades\Hash;

class ApiKeyMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $apiKey = $request->header('X-API-Key');

        if (!$apiKey) {
            return response()->json(['error' => 'API Key required'], 401);
        }

        $keyHash = hash('sha256', $apiKey);
        $key = ApiKey::where('key_hash', $keyHash)
            ->where('is_active', true)
            ->first();

        if (!$key) {
            return response()->json(['error' => 'Invalid API Key'], 401);
        }

        if ($key->expires_at && now()->gt($key->expires_at)) {
            return response()->json(['error' => 'API Key expired'], 401);
        }

        // Permission check based on HTTP method
        $method = $request->method();
        $permission = match($method) {
            'GET', 'HEAD' => 'read',
            'POST', 'PUT', 'PATCH' => 'write',
            'DELETE' => 'delete',
            default => 'read'
        };

        if (!in_array($permission, $key->permissions)) {
            return response()->json(['error' => 'Insufficient permissions'], 403);
        }

        // Attach to request
        $request->merge([
            'api_key_id' => $key->id,
            'project_id' => $key->project_id,
        ]);

        // Track usage
        $key->update([
            'last_used_at' => now(),
            'usage_count' => $key->usage_count + 1,
        ]);

        return $next($request);
    }
}
```

### RateLimitMiddleware (Redis Sliding Window)
```php
<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;

class RateLimitMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $key = 'rate_limit:' . ($request->header('X-API-Key') ?? $request->ip());
        $limit = 100; // requests per minute
        $window = 60; // seconds

        // Sliding window using Redis Sorted Set
        $now = microtime(true);
        $windowStart = $now - $window;

        // Remove old entries
        Redis::zremrangebyscore($key, 0, $windowStart);

        // Count current entries
        $current = Redis::zcard($key);

        if ($current >= $limit) {
            return response()->json([
                'error' => 'Rate limit exceeded',
                'retry_after' => ceil(Redis::zrange($key, 0, 0, 'WITHSCORES')[1] + $window - $now)
            ], 429);
        }

        // Add current request with timestamp
        Redis::zadd($key, $now, uniqid() . ':' . $request->ip());
        Redis::expire($key, $window);

        $response = $next($request);

        $response->headers->set('X-RateLimit-Limit', $limit);
        $response->headers->set('X-RateLimit-Remaining', max(0, $limit - $current - 1));
        $response->headers->set('X-RateLimit-Reset', ceil($windowStart + $window));

        return $response;
    }
}
```

---

## 🎨 VITE CONFIGURATION

```typescript
// vite.config.ts
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import path from 'path';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/js/app.ts'],
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
    ],
    resolve: {
        alias: {
            '@': path.resolve(__dirname, 'resources/js'),
            '@components': path.resolve(__dirname, 'resources/js/components'),
            '@pages': path.resolve(__dirname, 'resources/js/pages'),
            '@stores': path.resolve(__dirname, 'resources/js/stores'),
            '@utils': path.resolve(__dirname, 'resources/js/utils'),
            '@types': path.resolve(__dirname, 'resources/js/types'),
        },
    },
    server: {
        hmr: {
            host: 'localhost',
        },
    },
});
```

---

## 🚀 EXECUTION COMMAND FOR AI

Copy and paste this entire prompt into your AI tool, then type:

```
"Act as Principal System Architect. Begin Phase 1: Generate the complete directory hierarchy, multi-stage Dockerfile, docker-compose.yml, Nginx configuration, all database migrations with partitioning and indexes, TenantMiddleware, ApiKeyMiddleware, Redis Sliding-Window RateLimitMiddleware, Vite configuration, Vue SPA entry file, and web/api route files. Make it enterprise-grade with full production readiness."
```
