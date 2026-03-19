# Call Tracking & AI Analysis Dashboard

A sleek, professional Next.js 16 application for call tracking and AI-powered analysis. Think "mission control for sales calls" with a dark, authoritative design powered by deep navy and electric cyan colors.

## Features

- **Email/Password Authentication** - Secure session-based authentication
- **Call Management** - Browse, filter, and search call history
- **AI Analysis** - Automatic qualification scoring and analysis (0-100)
- **Live Monitoring** - Real-time call status updates with polling
- **Call Details** - Rich call information with transcripts and AI insights
- **Settings Management** - Store CTM and OpenRouter API credentials
- **Responsive Design** - Mobile-first approach with full responsive support
- **Dark Theme** - Navy (#0A1628) and cyan (#00D4FF) color system

## Tech Stack

- **Framework**: Next.js 16 (App Router)
- **Frontend**: React 19, TypeScript
- **Styling**: Tailwind CSS 4.2 with custom design tokens
- **Auth**: Jose (JWT) with HTTP-only cookies
- **UI Components**: Custom-built components (no shadcn/ui)
- **Deployment**: Vercel

## Getting Started

### Prerequisites

- Node.js 18+ with pnpm
- (Optional) CallTrackingMetrics API credentials
- (Optional) OpenRouter API key for AI analysis

### Installation

```bash
# Install dependencies
pnpm install

# Create .env.local
cat > .env.local << EOF
NEXTAUTH_SECRET=your-secret-key-min-32-chars
NEXTAUTH_URL=http://localhost:3000
EOF

# Run development server
pnpm dev
```

Open [http://localhost:3000](http://localhost:3000) in your browser.

### Demo Credentials

The app comes with demo credentials for testing:
- **Email**: demo@example.com
- **Password**: demo

Or use the "Try Demo Mode" button to skip authentication.

## Project Structure

```
app/
├── api/
│   ├── auth/
│   │   ├── login/route.ts
│   │   ├── logout/route.ts
│   │   └── session/route.ts
│   ├── analyze/route.ts
│   └── settings/route.ts
├── dashboard/
│   ├── layout.tsx
│   ├── page.tsx
│   ├── calls/[id]/page.tsx
│   ├── monitor/page.tsx
│   ├── history/page.tsx
│   └── settings/page.tsx
├── layout.tsx
├── page.tsx (login)
└── globals.css

components/
├── ui/
│   ├── Button.tsx
│   ├── Card.tsx
│   ├── Input.tsx
│   └── Toast.tsx
├── Navbar.tsx
├── ScoreCircle.tsx
├── StatsCard.tsx
├── CallTable.tsx
└── EmptyState.tsx

lib/
├── auth.ts (session management)
├── ai.ts (AI analysis service)
├── ctm.ts (CTM API client)
├── mockData.ts (demo data)
└── utils.ts

tailwind.config.ts
globals.css
```

## Color System

- **Primary Navy**: #0A1628
- **Background**: #050D18
- **Accent Cyan**: #00D4FF
- **Slate**: #64748B
- **Text**: #E2E8F0
- **Success**: #10B981
- **Warning**: #F59E0B
- **Error**: #EF4444

## Key Pages

### Login (/)]
Split-screen design with brand statement and login form. Supports demo mode.

### Dashboard (/dashboard)
Overview with stats cards and recent calls table with sorting.

### Call Detail (/dashboard/calls/[id])
Comprehensive call information including:
- Qualification score (hot/warm/cold)
- Caller details
- Full transcript with speaker labels
- AI analysis with sentiment, tags, and disposition
- Action buttons for follow-up

### Live Monitor (/dashboard/monitor)
Real-time call monitoring with:
- Status indicator
- Configurable polling interval
- Active call card
- Recent analysis history

### History (/dashboard/history)
Advanced search and filtering with:
- Phone number search
- Score range filtering
- Date range picker
- CSV export
- Pagination

### Settings (/dashboard/settings)
Credential management for:
- CTM API keys
- OpenRouter API key
- Theme preferences
- Credential clearing

## Authentication

Uses JWT tokens stored in HTTP-only cookies for secure session management:
- 24-hour expiration
- Automatic validation on dashboard routes
- Session persistence across tabs
- Secure logout with cookie clearing

## AI Analysis

The application includes a mock AI analysis service that generates:
- **Qualification Score** (0-100): Based on transcript keywords
- **Sentiment**: Positive/Neutral/Negative
- **Tags**: Hot/Warm/Cold lead classification
- **Suggested Disposition**: Next steps recommendation
- **Summary**: Human-readable analysis
- **Salesforce Notes**: Formatted for CRM integration

To integrate with OpenRouter API, update the `/lib/ai.ts` file with actual API calls.

## API Routes

### Auth
- `POST /api/auth/login` - Authenticate user
- `POST /api/auth/logout` - Clear session
- `GET /api/auth/session` - Get current session

### Analysis
- `POST /api/analyze` - Run AI analysis on transcript

### Credentials
- `POST /api/settings` - Save credentials (when integrated)
- `GET /api/settings` - Retrieve masked credentials (when integrated)

## Customization

### Adding Real API Integration

1. **CTM API**: Update `/lib/ctm.ts` with actual API endpoints
2. **OpenRouter**: Replace mock analysis in `/lib/ai.ts` with API calls
3. **Database**: Add Vercel Postgres integration for credential storage
4. **Authentication**: Replace with NextAuth.js if needed

### Styling

All colors use CSS custom properties in `app/globals.css`. Update the color variables to change the theme system-wide. Tailwind classes use the defined color palette.

### Adding Users

Currently uses in-memory user storage. To add persistence:
1. Set up Vercel Postgres
2. Create users table
3. Hash passwords with bcrypt
4. Update `/lib/auth.ts` with database queries

## Deployment

### Deploy to Vercel

```bash
# Push to GitHub
git push origin main

# Deploy from Vercel dashboard or:
vercel
```

### Environment Variables

Set in Vercel dashboard:
- `NEXTAUTH_SECRET` - Generate with: `openssl rand -base64 32`
- `NEXTAUTH_URL` - Your production URL

Optional for integrations:
- `DATABASE_URL` - Vercel Postgres connection string
- `OPENROUTER_API_KEY` - For real AI analysis
- `CTM_ACCESS_KEY`, `CTM_SECRET_KEY` - CallTrackingMetrics credentials

## Performance Optimizations

- Image optimization with Next.js Image component
- Code splitting with dynamic imports
- Server-side rendering for initial page load
- Client-side caching with SWR (when integrated)
- CSS-in-JS minimization with Tailwind
- Viewport-based responsive design

## Security

- HTTP-only cookies for session tokens
- CSRF protection via SameSite cookies
- Secure password handling (bcrypt when using DB)
- Environment variable isolation
- No sensitive data in client-side code
- Input validation on all API routes

## Browser Support

- Chrome/Edge (latest)
- Firefox (latest)
- Safari (latest)
- Mobile browsers (iOS Safari, Chrome Mobile)

## License

MIT

## Support

For issues or feature requests, please visit https://vercel.com/help
