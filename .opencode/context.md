# Project Context — bob-ags

## Environment
- **Language:** PHP 8.2+
- **Framework:** Laravel 12.x
- **Frontend:** Vue 3 + Vite + Tailwind CSS
- **Package Manager:** Composer + npm
- **Build:** `npm run build` (Vite)
- **Test:** `php artisan test` (PHPUnit)
- **E2E:** `npx playwright test`
- **Dev:** `npm run dev` (concurrently: Vite + PHP server + queue listener)
- **Lint:** `./vendor/bin/pint`

## Project Type
- **Application (Web)** — Call Tracking & QA Analysis system

## Infrastructure
- **Container:** None
- **CI/CD:** GitHub Actions (`.github/workflows/tests.yml`)
- **Database:** SQLite (`:memory:` for tests/dev); PostgreSQL (prod)
- **Queue:** Database driver
- **Session:** Database driver

## Structure
- **Source:** `app/`
- **Tests:** `tests/` (Feature + Unit)
- **Config:** `config/`
- **Routes:** `routes/web.php`, `routes/auth.php`
- **Migrations:** `database/migrations/`

## Key Architecture
- **Auth:** Laravel Breeze (session-based, `web` guard)
- **User Model:** `App\Models\User` (Eloquent Authenticatable)
- **User Fields:** `name`, `email`, `password`, `role`, `ctm_agent_id`
- **Roles:** admin, qa, viewer, supervisor
- **Middleware:** Custom `role` middleware (`role:admin,qa,...`)
- **Providers:** `AppServiceProvider` only (minimal setup)
- **Middleware Registration:** `bootstrap/app.php`

## External Services (Integrated)
- **CTM API** — Call tracking data (cursor-paginated)
- **OpenAI** — Whisper transcription + QA analysis (`gpt-4o-mini`)
- **Anthropic** — OpenRouter (configured but not actively used)

## Key Files
- `app/Services/CTMService.php` — CTM API client
- `app/Services/OpenAIService.php` — OpenAI Whisper + QA
- `app/Services/QAAnalysisService.php` — QA orchestration
- `app/Http/Controllers/CallController.php` — Call search/sync/transcribe/analyze
- `app/Jobs/TranscribeCallJob.php` — Background transcription
- `app/Http/Controllers/RecordingController.php` — Audio proxy
- `resources/views/calls/index.blade.php` — Call search UI
- `resources/views/calls/show.blade.php` — Call detail + action buttons

## Conventions
- **Naming:** snake_case for files, PascalCase for classes
- **Imports:** Fully qualified class names
- **Error handling:** Exceptions + logging
- **Testing:** PHPUnit with SQLite `:memory:` (sessions table conflict in Feature tests)
- **Auth:** Email/password via Breeze (Azure AD integration requested)

## Existing Auth Setup
- Uses Breeze auth (session-based)
- Login at `/login` (GET/POST)
- Register at `/register` (GET/POST)
- Logout at `/logout` (POST)
- Routes in `routes/auth.php`, included via `routes/web.php`
- Session controller: `app/Http/Controllers/Auth/`
- Session driver: database

## Testing
- **Known issue:** Feature tests may fail with SQLite `:memory:` due to sessions table conflict across test requests (pre-existing, framework-level)
- External services mocked (CTM API, OpenAI, Anthropic)
