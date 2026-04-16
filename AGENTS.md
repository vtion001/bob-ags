# BOB-AGS — Call Tracking & QA Analysis

## Watch & Validate
**Test command:**
```bash
php artisan test
```
**E2E command:**
```bash
npx playwright test
```
**Report:** `test-results/`
**Skills:** systematic-debugging, verification-before-completion, test-master

## Key Commands
- **Dev:** `npm run dev` (starts Vite + Laravel server + queue listener concurrently)
- **Tests:** `php artisan test` (runs PHPUnit Feature + Unit suites)
- **E2E:** `npx playwright test`
- **Lint:** `./vendor/bin/pint`
- **Queue:** `php artisan queue:listen --tries=1 --timeout=0`
- **Migrate:** `php artisan migrate`
- **DB:** SQLite (`:memory:`) for tests/dev; PostgreSQL for prod

## Testing
- Uses SQLite in-memory (`:memory:`) for PHPUnit — no real DB needed
- **Known pre-existing failure:** Feature tests fail with SQLite `:memory:` sessions (sessions table conflict across test requests). This is a framework-level issue with the default Breeze auth tests, not application code.
- External services mocked where possible (CTM API, OpenAI, Anthropic)
- `DB_CONNECTION=sqlite`, `DB_DATABASE=:memory:`, `QUEUE_CONNECTION=sync`

## Key Files
- `app/Services/CTMService.php` — CTM API client (pagination, agent lookups)
- `app/Http/Controllers/CallController.php` — search, sync, transcribe, analyze
- `app/Services/OpenAIService.php` — Whisper transcription + QA scoring (both via OpenAI `gpt-4o-mini`)
- `app/Services/QAAnalysisService.php` — QA orchestration (uses OpenAIService)
- `app/Jobs/TranscribeCallJob.php` — Background transcription job
- `app/Http/Controllers/RecordingController.php` — Recording audio proxy
- `resources/views/calls/index.blade.php` — Call search results + CTM pagination UI
- `resources/views/calls/show.blade.php` — Call detail + transcribe/QA buttons

## Architecture
- Laravel 12 + Vue 3 + Vite + Tailwind CSS
- CTM API: cursor-paginated (10 calls/page). Use `getAllCalls()` for full result sets; `getCalls()` only returns the first page (10 calls max).
- OpenAI: both Whisper transcription and QA analysis use `OPENAI_API_KEY` from `.env` (via `OpenAIService`)
- Recording audio routed through `RecordingController`
- CTM filtered calls: upsert-on-view pattern for unsynced calls in `CallController::show()`
- Agent ID format: CTM returns raw numeric IDs; `getAgentById()` converts to hashed format; call records store hashed IDs

---

# Testing Automation — watch-and-validate

A generic, framework-agnostic test-fix-validate automation system. Watches your project files for changes and triggers opencode to autonomously run tests, fix failures, and produce a structured report.

## Quick Start

### 1. Install dependencies

```bash
cd scripts
npm install
```

### 2. Run once (manual / cron)

```bash
node watch-and-validate.js --once --project /path/to/my-project
```

### 3. Run as daemon (file watcher)

```bash
node watch-and-validate.js --daemon --project /path/to/my-project
```

Changes to `AGENTS.md` or source files trigger an opencode session automatically.

---

## Project Setup

For the workflow to work, each project needs an `AGENTS.md` file in its root. The `workflow-prompt.md` reads this file to understand how to run tests for your project.

### Minimal `AGENTS.md` setup

Add a `## Watch & Validate` section to your project's `AGENTS.md`:

```markdown
## Watch & Validate

**Test command:** `./vendor/bin/phpunit && npx playwright test`
**E2E command:** `npx playwright test`
**Report:** `test-results/`
**Skills:** systematic-debugging, verification-before-completion, test-master
```

### Recommended `AGENTS.md` sections the workflow reads

The `workflow-prompt.md` uses these sections from `AGENTS.md`:

| Section | Used For |
|---------|----------|
| `## Key Commands` | Finding test commands |
| `## Testing` | Special test config, env vars, seed data |
| `## Key Files` | Files relevant to failing tests |
| `## Architecture` | Understanding project structure |

---

## Workflow

```
File changes detected (AGENTS.md or source files)
        ↓
  Debounce (500ms)
        ↓
  Spawn opencode run --file workflow-prompt.md
        ↓
  opencode reads AGENTS.md → extracts test commands
        ↓
  Run test suite → capture output
        ↓
  [If FAIL] Load systematic-debugging → diagnose
        ↓
  [If FAIL] Apply fix → re-run tests
        ↓
  [Max 3 iterations]
        ↓
  Write test-results/YYYYMMDD-HHMMSS-summary.json
        ↓
  Stream output to terminal (real-time)
```

---

## Commands Reference

### Watch modes

| Command | Description |
|---------|-------------|
| `--daemon` | Background file watcher (default) |
| `--once` | Run once on startup, then exit (for cron) |

### Options

| Option | Description |
|--------|-------------|
| `--project, -p <path>` | Project root (can be repeated) |
| `--debounce, -d <ms>` | Debounce delay in ms (default: 500) |
| `--verbose, -v` | Enable verbose output |
| `--help, -h` | Show help |
| `-- <args>` | Pass args directly to `opencode run` |

### Examples

```bash
# Single project, daemon mode
node watch-and-validate.js --project ./my-laravel-app

# Multiple projects, daemon mode
node watch-and-validate.js -p ./app-a -p ./app-b -p ./app-c

# Cron: run every 5 minutes
*/5 * * * * cd ~/testing-automation && node scripts/watch-and-validate.js --once --project /path/to/project

# Cron: run on git push (post-receive hook)
git push origin main && cd ~/testing-automation && node scripts/watch-and-validate.js --once --project /path/to/project

# With custom opencode model
node scripts/watch-and-validate.js --once --project ./my-project -- --model anthropic/claude-3

# With custom agent
node scripts/watch-and-validate.js --once --project ./my-project -- --agent worker

# Watch multiple sub-projects in a monorepo
node scripts/watch-and-validate.js --project ./packages/core --project ./packages/web --project ./packages/api
```

---

## Report Format

Reports are written to `test-results/` in each project root:

```json
{
  "timestamp": "2026-04-16T23:42:00.000Z",
  "trigger": "AGENTS.md change",
  "session_id": "abc123",
  "tests_passed": true,
  "test_command": "./vendor/bin/phpunit && npx playwright test",
  "iterations": 2,
  "total_test_count": 61,
  "passed_count": 61,
  "failed_count": 0,
  "risky_count": 4,
  "fixes_applied": [
    {
      "file": "app/Jobs/GenerateBlogBatch.php",
      "issue": "missing tenant_id on child Content records",
      "fix": "Added tenant_id to Content::create() calls",
      "root_cause": "BelongsToTenant global scope was hiding orphaned children",
      "iteration": 1
    }
  ],
  "test_output_snippet": "...",
  "summary": "All tests passed — 61 tests (61 passed, 4 risky)"
}
```

---

## Architecture

```
testing-automation/
├── scripts/
│   ├── watch-and-validate.js   # Node.js daemon (chokidar + opencode)
│   ├── workflow-prompt.md      # Reusable opencode prompt (framework-agnostic)
│   ├── summary-schema.json     # JSON schema for reports
│   └── package.json
└── README.md
```

### `watch-and-validate.js`

- Pure Node.js — no external framework needed
- Uses `chokidar` for cross-platform file watching
- Spawns `opencode run --file workflow-prompt.md` per project
- Streams opencode output in real-time
- Tracks running processes per project (no double-runs)
- Writes structured JSON summary to `test-results/`
- Graceful `Ctrl+C` shutdown

### `workflow-prompt.md`

- Framework-agnostic — works with any language (PHP, JS, Python, Go, etc.)
- Reads project context from `AGENTS.md` at runtime
- Loads skills dynamically: `systematic-debugging`, `verification-before-completion`, `test-master`
- Iterates: fix → test → repeat (max 3 cycles)
- Writes report and reports final status

---

## Multi-Project Support

Each `AGENTS.md` found in a watched directory tree becomes a separate project root:

```
/monorepo/
  ├── packages/
  │   ├── core/AGENTS.md    ← project root
  │   ├── web/AGENTS.md     ← project root
  │   └── api/AGENTS.md      ← project root
  └── shared/AGENTS.md       ← project root
```

Each project is watched independently. A change to `packages/web/src/app.js` triggers validation only for the `web` project.

---

## Platform Notes

### Windows
```powershell
# Daemon
node scripts\watch-and-validate.js --daemon --project .\my-project

# Cron (use Task Scheduler instead of cron)
# Create a task that runs: node scripts\watch-and-validate.js --once --project .\my-project
```

### Linux / macOS
```bash
# Daemon
./scripts/watch-and-validate.js --daemon --project ./my-project

# Cron
*/5 * * * * cd /home/user/testing-automation && node scripts/watch-and-validate.js --once --project /path/to/project
```

### Git Hooks (post-commit / post-push)
```bash
# .git/hooks/post-commit
cd "$(git rev-parse --show-toplevel)" && /path/to/testing-automation/scripts/watch-and-validate.js --once --project .

# .git/hooks/post-push
cd "$(git rev-parse --show-toplevel)" && /path/to/testing-automation/scripts/watch-and-validate.js --once --project .
```

---

## Environment Variables

| Variable | Default | Description |
|----------|---------|-------------|
| `OPENCODE_BIN` | `opencode` | Path to opencode binary |
| `REPORT_DIR` | `test-results/` | Override report directory |

---

## Troubleshooting

### "chokidar not found"
```bash
cd scripts
npm install
```

### "opencode not found"
Ensure `opencode` is in your PATH, or set:
```bash
set OPENCODE_BIN=C:\path\to\opencode.exe
```

### Test still fails after 3 iterations
The workflow stops and writes a report with `tests_passed: false`. Review the `test_output_snippet` and `fixes_applied` in the report to diagnose manually.

### Daemon doesn't restart on file change
Check that the path patterns are correct. Use `--verbose` to see which files are being watched.

### opencode session hangs
The session has a 60-minute timeout. Kill it and re-run with `--verbose` to see where it stalled.
