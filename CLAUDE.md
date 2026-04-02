# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

BOB (Business Operations Butler) is a Next.js 16 call tracking and AI-powered quality assurance dashboard for substance abuse helpline calls. It integrates with CallTrackingMetrics (CTM) for call data and AssemblyAI for real-time transcription, with AI scoring via OpenRouter.

**Tech Stack**: Next.js 16 (App Router), React 19, TypeScript, Tailwind CSS 4, Supabase (SSR auth), AssemblyAI, OpenRouter

## Commands

```bash
pnpm dev      # Start development server
pnpm build    # Production build
pnpm start    # Start production server
pnpm lint     # Run ESLint
```

## Architecture

### Directory Structure

```
app/
├── api/
│   ├── auth/           # Login, logout, session endpoints
│   ├── ctm/            # CTM API proxy routes (calls, agents, numbers, etc.)
│   └── openrouter/     # AI analysis endpoint
├── dashboard/          # Protected dashboard pages
│   ├── calls/[id]/     # Call detail page
│   ├── monitor/        # Live call monitoring
│   ├── history/        # Call history with search
│   ├── settings/       # User settings & credentials
│   ├── agents/         # Agent management
│   └── qa-logs/        # QA analysis logs
├── auth/              # Auth pages (signup, callback)
└── page.tsx           # Login page

components/
├── ui/                 # Base UI components (Button, Card, Input, etc.)
├── dashboard/          # Dashboard-specific components
├── call-detail/        # Call detail page components
├── monitor/            # Live monitoring components
├── settings/           # Settings page components
└── agents/             # Agent management components

lib/
├── supabase/           # Supabase client helpers (server.ts, client.ts, middleware.ts)
├── ctm/               # CTM API client and services
│   └── services/      # calls, agents, numbers, schedules, etc.
├── ai/                # AI analysis (OpenRouter + keyword fallback)
├── realtime/          # Real-time call analysis (AssemblyAI streaming)
├── calls/             # Call data fetching, caching, transformations
├── rag/               # RAG knowledge base for suggestions
└── auth.ts            # Custom HMAC-based session management (legacy)

hooks/
├── dashboard/          # useCalls, useActiveCalls, useDashboardStats, etc.
├── monitor/           # useLiveAnalysis, useLiveAIInsights, useMonitorPage
└── calls/             # useCallDetail

supabase/
├── migrations/         # Database schema migrations
└── functions/         # Edge Functions (on-auth-signup)
```

### Authentication

Two auth systems coexist:
1. **Supabase SSR** (`@supabase/ssr`) - Primary auth, configured in `middleware.ts` and `lib/supabase/server.ts`
2. **Legacy HMAC sessions** (`lib/auth.ts`) - Custom session tokens for developer login

The middleware at `middleware.ts` creates a Supabase server client and refreshes sessions on every request. **Must use `getSession()` (not `getUser()`)** to refresh cookies.

### CTM Integration

`lib/ctm/client.ts` is the base class; `lib/ctm/services/*.ts` contain feature-specific clients (CallsService, AgentsService, etc.). API routes in `app/api/ctm/` proxy requests to CTM to hide credentials.

### AI Analysis System

`lib/ai/` handles call scoring with:
- **OpenRouter** as primary AI analyzer (Claude-3-Haiku)
- **Keyword fallback** when AI fails
- **25-criterion rubric** for QA scoring (see `docs/AI_SCORING_SYSTEM.md`)

ZTP (Zero Tolerance Policy) criteria (3.4, 5.1, 5.2) auto-fail calls if violated.

### Realtime Analysis

`lib/realtime/` handles live call transcription via AssemblyAI streaming. Uses a rubric-based analyzer (`analyzer.ts`) that detects keywords in real-time and calculates live QA scores.

### Database (Supabase)

Migrations in `supabase/migrations/` define the schema. Key tables:
- `users` / `user_roles` - Authentication and authorization
- `agent_profiles` - CTM agent mappings
- `user_settings` - Per-user preferences and credentials
- `live_analysis_logs` - Realtime analysis history
- `qa_overrides` - Manual QA score overrides

## Environment Variables

```bash
NEXTAUTH_SECRET=              # Session encryption key
NEXTAUTH_URL=                 # Application URL
NEXT_PUBLIC_SUPABASE_URL=     # Supabase project URL
NEXT_PUBLIC_SUPABASE_ANON_KEY= # Supabase anon key
CTM_ACCESS_KEY=               # CTM API access key
CTM_SECRET_KEY=               # CTM API secret key
CTM_ACCOUNT_ID=               # CTM account ID
OPENROUTER_API_KEY=           # OpenRouter API key for AI
ASSEMBLYAI_API_KEY=           # AssemblyAI API key
```

## Key Patterns

**Server vs Client Components**: Most dashboard components are `'use client'`. API routes in `app/api/` are server-side.

**Supabase Server Client Pattern**:
```typescript
// In API routes, use the proxy pattern:
const { supabase, response } = await createServerSupabase(request)
return response
```

**CTM Service Pattern**:
```typescript
const callsService = createCallsService()
const calls = await callsService.getCalls({ hours: 24, limit: 100 })
```

**Live Monitoring**: Uses polling with `useMonitorPage` hook, not WebSockets. AssemblyAI streaming via `lib/realtime/assemblyai-realtime.ts`.

## Color System

Dark navy theme with cyan accents:
- Navy 900: `#0A1628` (primary)
- Background: `#050D18`
- Cyan Accent: `#00D4FF`

## Demo Credentials

- Email: `demo@example.com`
- Password: `demo`
- Dev credentials: `agsdev@allianceglobalsolutions.com` / `ags2026@@`
