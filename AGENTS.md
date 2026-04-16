# AGENTS.md - BOB-AGS

BOB-AGS is a Laravel 12 call-center management app (Vue 3 + Tailwind + Vite, SQLite local DB, Azure Redis).

## Stack and Entry Points

- App entry: php artisan serve -> http://localhost (config in .env)
- Frontend assets: Vite + Vue 3 + Tailwind CSS (resources/js/app.js, resources/css/app.css)
- Backend: Laravel 12, PHP 8.2+; autoloads app/ via PSR-4
- DB: SQLite via DB_CONNECTION=sqlite (.env). Migrations in database/migrations/. Run with php artisan migrate.
- Key routes: routes/web.php (main app), routes/auth.php (Breeze auth). API routes under /api prefix. Webhooks (/webhooks/ctm, /webhooks/assemblyai) bypass CSRF.
- Models: app/Models/ - User, Call, Agent, LiveMonitoring, LiveTranscript, QaLog, KnowledgeBase, KnowledgeBaseEntry, Setting
- Services: app/Services/ - OpenAIService, AnthropicService, OpenRouterService, AssemblyAIService, CTMService, QAAnalysisService, LiveMonitorService, AISuggestionService, KnowledgeBaseService, ZTPAlertService

## Commands

# Setup (after clone)
composer install
php artisan key:generate
php artisan migrate
npm install
npm run build

# Run dev (Vite + Laravel server + queue listener + log tailer concurrently)
npm run dev

# Tests
composer test
php artisan test
php artisan test --filter=SomeTestClass     # single test
php artisan test --testsuite=Unit           # unit or feature only

# Code style
./vendor/bin/pint                            # Laravel preset, no_unused_imports disabled

## E2E Tests (Playwright)

- Config: playwright.config.ts - Chromium only, base URL http://localhost:8000
- Test dir: tests/e2e/
- Run: npx playwright test (starts php artisan serve automatically via webServer config)
- Workers: 1 (sequential, not parallel)

## CI

- .github/workflows/tests.yml - runs PHPUnit on PHP 8.2, 8.3, 8.4 (master + *.x branches + PRs)
- StyleCI: .styleci.yml uses Laravel preset; no_unused_imports rule is disabled

## AI Provider Configuration

Three providers via env vars (AI_PROVIDER selects active one):

- AI_PROVIDER: openai / anthropic / openrouter
- OPENAI_API_KEY, ANTHROPIC_API_KEY, OPENROUTER_API_KEY, ASSEMBLYAI_API_KEY

## Role-Based Access

Middleware role:role1,role2,... gates routes. Roles: admin, qa, supervisor.

- /supervisor/* -> role:supervisor,admin
- /agents/* -> role:qa,admin
- /knowledge-base/* -> role:admin
- All auth-gated routes require @role middleware.

## External Integrations

- **CallTrackingMetrics**: CTM_API_HOST, CTM_ACCESS_KEY, CTM_SECRET_KEY, CTM_ACCOUNT_ID in .env
- **CTM API pagination**: Returns max 10 calls per page via cursor (`next_page` URL). `CTMService::getCalls()` fetches first page only. `CTMService::getAllCalls()` handles cursor pagination (max 500 pages = 5,000 calls). `searchCTM` uses 500 pages; results paginated at 100 per page in the UI with prev/next/page-number navigation that preserves all filter params.
- **Azure Redis**: REDIS_CLIENT=phpredis, REDIS_TLS=true

## Key Conventions

- **DB**: Local dev uses **SQLite** (`DB_CONNECTION=sqlite`). Tests run against **in-memory SQLite** (`:memory:`). Production uses **PostgreSQL** (Supabase via `AZURE_POSTGRES_*` env vars).
- **Auth**: Standard Laravel Breeze auth. Custom `role` middleware gates `qa`, `supervisor`, `admin` routes.
- **CSRF**: Webhook routes (`/webhooks/ctm`, `/webhooks/assemblyai`) are outside auth middleware — no CSRF protection.
- **E2E tests**: Run against `php artisan serve` on port 8000 via Playwright webServer config.
- **Feature tests**: Known pre-existing failure with SQLite `:memory:` sessions table — run `--testsuite=Unit` to isolate.

## Team (Scrum + Kanban)

- Product Owner: Travis
- TPM / Scrum Master: Codey
- DevTeam: Syntax (Principal Eng), Aesthetica (Frontend/UX), Sentinal (Security), Flow (DevOps), Verity (QA)
- MarketingTeam: Codey, Bran (SEO/AEO/Schema), Cipher (StoryBrand), Echo (Content)
- DeploymentTeam: Flow, Sentinal, Syntax, Verity
- Definition of Done: code reviewed, tests passing, security review, deployed to staging, PO accepted
