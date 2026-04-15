# Design: Document Picture-in-Picture Floating Window

**Date:** 2026-04-15  
**Status:** Implemented

## Problem

The BOB AI Assistant floating widget is a browser-sandboxed overlay. When agents minimize Chrome or switch to another app, the widget disappears — forcing them to re-focus the browser mid-call.

## Solution

Chrome/Edge 116+ ships the `documentPictureInPicture` API, which opens an OS-level always-on-top window containing arbitrary HTML. Clicking a pop-out button in the widget header detaches it into this floating OS window. The window stays visible above all apps, even when Chrome is minimized.

## Constraints

- **Browser:** Chrome/Edge 116+ only. The pop-out button is hidden via feature detection on unsupported browsers — no degradation in Firefox/Safari.
- **No new files:** All changes are confined to `resources/views/live-monitoring/partials/floating-window.blade.php`.
- **No framework:** Vanilla JS, same as the existing code.

## Architecture

All JS logic (SSE stream, fetch calls, timer) stays in the main page's IIFE. A `_doc` variable abstracts which document the logic reads from and writes to:

- Default: `_doc = document` → updates the in-page widget
- PiP mode: `_doc = pipWindow.document` → updates the PiP clone

The SSE connection and all `fetch()` calls always run from the main page's browsing context. The CSRF token is always read from `document` (not `_doc`).

## Components

### Pop-out button
Added to `.floating-header-right` between the timer and close button. Hidden by default; shown when `'documentPictureInPicture' in window` is true. SVG icon toggles between pop-out (↗) and return (↙) states.

### `enterPipMode()`
1. Calls `documentPictureInPicture.requestWindow({ width: 400, height: 580 })`
2. Copies all stylesheets from the main document into the PiP window's `<head>`
3. Deep-clones `#floatingWindow` into PiP's `<body>` with `position: static`
4. Sets `_doc = pipWin.document`
5. Re-wires all button `onclick` handlers (inline attributes don't clone across documents)
6. Hides the in-page widget
7. Listens for `pagehide` on the PiP window to auto-call `exitPipMode()`

### `exitPipMode()`
1. Sets `_doc = document`
2. Shows the in-page widget
3. Resets the pop-out button icon
4. Closes the PiP window if still open

### Drag behavior
The in-page drag listeners check `isPipMode` and do nothing when true. In PiP mode, the OS handles window dragging natively.

## Verification

1. Open live monitoring page in Chrome 116+
2. Click the pop-out icon (↗) in the widget header → Chrome prompts once for permission → PiP window appears
3. Minimize Chrome → PiP window remains visible above all other apps
4. Verify inside PiP: transcript updates, refresh suggestions (streaming), chat input (streaming), ZTP alerts, timer
5. Close PiP window via OS ✕ → in-page widget reappears automatically
6. Test in Edge (same Chromium engine) → identical behavior
7. Open in Firefox → pop-out button not shown; widget works normally
