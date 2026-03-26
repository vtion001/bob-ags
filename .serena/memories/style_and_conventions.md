# Style and Conventions - bob-ags

## TypeScript

- Strict mode enabled
- Prefer explicit types over `any`
- Use `interface` for object shapes, `type` for unions

## Naming

- PascalCase for components and TypeScript files
- camelCase for functions and variables
- kebab-case for file names (rarely used)

## Components

- Use 'use client' directive for client-side components
- JSDoc comments for component props
- Custom UI components in `components/ui/`

## Styling

- Tailwind CSS 4.2 with custom design tokens
- CSS variables defined in `app/globals.css`
- Navy color scale in `tailwind.config.ts`

## API Routes

- App Router route handlers in `app/api/`
- JWT session validation on protected routes
- HTTP-only cookies for session tokens
