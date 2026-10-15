# Browser regression tests

Tests live in `tests/browser/` and run against an **isolated testing application**, not production.
They cover public navigation, error-page recovery, theme persistence, desktop/mobile overflow,
invalid login and registration/logout state. Existing Laravel feature tests remain required.

## Run locally

1. Back up your development data. Use a dedicated test database and testing .env.
2. Run Laravel, migrations and Vite build against that test environment.
3. Install dependencies with `npm install`, then `npx playwright install chromium`.
4. Run `npm run test:browser` with `PLAYWRIGHT_BASE_URL=http://127.0.0.1:8000` (or your local test URL).
5. Run `npm run test:browser:report` to inspect failures.

The auth test creates a temporary account. Never aim these tests at a live production site.
On CI, the workflow creates a disposable SQLite database and starts a local PHP server.

Browser checks are not exhaustive. Manually test private groups, media upload, post creation,
invitations, messaging, notifications, dark/light mode, and long-text layouts in both languages.
New UI features should gain matching regression tests before merging.

## Verification gates

- `php artisan test`
- `npm run build`
- `npm run test:browser`
- `composer audit` and `npm audit`

Upload Playwright traces/screenshots for browser failures; do not consider merely committed
tests proof that they pass.
