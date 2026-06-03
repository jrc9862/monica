# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## resources/js/ directory

### Structure

| Path              | Purpose                                                                                       |
| ----------------- | --------------------------------------------------------------------------------------------- |
| `Pages/`          | Inertia page components, mirroring the URL hierarchy (`Vault/`, `Settings/`, `Auth/`, `API/`) |
| `Shared/`         | Reusable Vue components shared across pages                                                   |
| `Shared/Modules/` | Contact-page module components (one per data domain: Notes, Calls, Goals, etc.)               |
| `Layouts/`        | Page shell layouts (`AppLayout.vue`, `Layout.vue`, etc.)                                      |
| `methods.js`      | Global utilities: `flash()` for toast messages, `isDark()`, WebAuthn helpers                  |
| `app.js`          | Inertia client bootstrap                                                                      |
| `ssr.js`          | SSR entry point                                                                               |

### Key patterns

- **No direct API calls from pages.** Data arrives via Inertia props from the server; mutations go through Inertia form submissions or `router.visit()`.
- **Flash messages** — use `flash(message, level)` from `methods.js` via the `mitt`/`tiny-emitter` event bus. The `Toaster.vue` in `Shared/` listens for `'flash'` events.
- **i18n** — use `trans()` from `laravel-vue-i18n` for all user-facing strings.
- **Icons** — use `lucide-vue-next` components (already installed).
- **Named routes** — use Ziggy's `route('route.name', params)` helper, which is globally available.

### Tailwind CSS v4

Classes are processed at build time by `@tailwindcss/vite`. There is no `tailwind.config.js`; add custom design tokens via CSS variables in the source stylesheets.
