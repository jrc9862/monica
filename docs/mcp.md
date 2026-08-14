# MCP endpoint: deployment and setup

Monica exposes a Model Context Protocol (MCP) server at `POST /mcp` (streamable
HTTP, via `laravel/mcp`). It lets Claude (claude.ai, Claude Desktop, mobile, and
Claude Code) read and write vault data through defined tools instead of the
web UI.

This doc covers deploying the endpoint on the existing Raspberry Pi 5 / docker
compose / Cloudflare Tunnel setup, and connecting clients to it.

## Deployment topology

- App runs on a Pi 5 via `docker compose` (`app`, `queue`, `scheduler`,
  `mariadb`, `redis` — see `docker-compose.yml`).
- `cloudflared` runs on the Pi host (outside this repo) and tunnels the public
  hostname to the `app` container.
- The public hostname sits behind a Cloudflare Access application requiring
  email-OTP MFA for the whole site.
- `/mcp` auth accepts either:
  - a Passport OAuth 2.1 bearer token (claude.ai, Claude Desktop, mobile
    custom connector), or
  - a Sanctum personal access token (Claude Code).

  See `routes/ai.php` — the route is `auth:api,sanctum` behind
  `AdvertiseResourceMetadata` and `throttle:mcp`.

## 1. Rebuild and migrate

```bash
docker compose build
docker compose up -d
docker compose exec app php artisan migrate --force
```

The migration adds Passport's `oauth_*` tables (oauth_clients, oauth_access_tokens,
oauth_refresh_tokens, oauth_auth_codes, oauth_device_codes) used by the new OAuth flow.

## 2. Passport keys

Passport signs OAuth tokens with an RSA keypair. Generate it once:

```bash
docker compose exec app php artisan passport:keys
```

By default (see `config/passport.php`, `PASSPORT_PRIVATE_KEY` /
`PASSPORT_PUBLIC_KEY` unset) Passport writes `oauth-private.key` and
`oauth-public.key` to `storage_path()`, i.e.
`/var/www/html/storage/oauth-{private,public}.key` inside the `app`
container. That path is inside the `monica-storage` named volume that
`docker-compose.yml` already mounts on every service (`app`, `queue`,
`scheduler`), so **no extra config is needed** — the keys survive
`docker compose build` / container recreation as long as the volume isn't
removed.

Only set `PASSPORT_PRIVATE_KEY` / `PASSPORT_PUBLIC_KEY` in `.env` if you want
the keys to live outside the volume (e.g. injected from a secrets manager, or
if you ever run `app` without the `monica-storage` volume mounted). If you do,
set them to the full PEM content with `\n` as literal two-character escapes
(Passport unescapes `\\n` to real newlines — see
`PassportServiceProvider::makeCryptKey()`), not a file path.

Do not run `passport:keys` again after a real deployment — regenerating
overwrites the keys and invalidates every previously issued OAuth token.

## 3. `.env` changes

```
APP_TRUSTED_PROXIES=*
APP_URL=https://<hostname>
```

`APP_TRUSTED_PROXIES=*` is required — without it Laravel sees the request as
coming from the `cloudflared`/docker network hop, not the real client, and
generates `http://` URLs / gets the client IP wrong (which also breaks the
`throttle:mcp` rate limiter, which keys on IP for unauthenticated requests).

`APP_URL` must be the real public HTTPS hostname — it's used verbatim in the
OAuth discovery documents (`issuer`, `authorization_endpoint`,
`token_endpoint`, `registration_endpoint` in `routes/mcp-oauth.php`).

## 4. Cloudflare Zero Trust configuration

The site-wide Access app (email-OTP) already covers the whole hostname. `/mcp`
needs to also allow two non-human callers through: Claude Code (bearer token
auth, no browser) and Anthropic's own infrastructure fetching MCP resources
server-to-server. Cloudflare Access evaluates the most specific matching path,
so a new, more specific Access application on the MCP paths overrides the
site-wide app just for those paths.

### a. Create a service token

Zero Trust dashboard → **Access → Service Auth → Service Tokens** → Create
service token. Note the generated **Client ID** and **Client Secret** — you
need both for the Claude Code connector config, and Cloudflare only shows the
secret once.

### b. Create a scoped Access application

**Access → Applications** → Add an application → Self-hosted.

Scope it to these paths on the Monica hostname (Access supports multiple path
matchers per application):

- `/mcp`
- `/oauth/token`
- `/oauth/register`
- `/.well-known/oauth-authorization-server` and its subpaths
- `/.well-known/oauth-protected-resource` and its subpaths

Do **not** include `/oauth/authorize` — see the note at the end of this
section.

### c. Policy 1 — "Claude Code" (Service Auth)

Action: **Service Auth**. Include: the service token created in step (a).
This lets Claude Code's HTTP client through with the
`CF-Access-Client-Id` / `CF-Access-Client-Secret` headers, no browser/OTP
involved.

### d. Policy 2 — "Anthropic egress" (Bypass)

Action: **Bypass**. Include: IP range `160.79.104.0/21`.

This lets claude.ai's own servers reach the OAuth discovery endpoints and
`/oauth/token` directly (they can't complete a Cloudflare Access browser
challenge or send service-token headers during the automated parts of the
OAuth flow). Order policies so Bypass and Service Auth are evaluated before
any default site-wide rule would apply to these paths.

### e. Why `/oauth/authorize` stays out of this app

`/oauth/authorize` is the human consent screen (Passport, rendered by
`oauth.authorize` view — see `Passport::authorizationView()` in
`AppServiceProvider`). It must stay behind the site-wide Access app so it
still requires the email-OTP challenge, and Passport additionally requires an
authenticated Monica login (session) before showing the Authorize/Deny
buttons. This is the human-in-the-loop step of the OAuth flow: anyone who
reaches it still needs both Cloudflare OTP and Monica credentials, regardless
of the Bypass policy on the other paths.

## 5. Connecting claude.ai / Claude Desktop / mobile

Settings → Connectors → Add custom connector → URL:

```
https://<hostname>/mcp
```

Claude drives the standard OAuth 2.1 + dynamic client registration flow:
discovers `/.well-known/oauth-protected-resource` and
`/.well-known/oauth-authorization-server`, registers a client via
`POST /oauth/register` (redirect URI must be `https://claude.ai/api/mcp/auth_callback`,
`http://localhost`, or `http://127.0.0.1` — see the `$allowed` check in
`routes/mcp-oauth.php` for the full validation; anything else is rejected), then opens a browser to
`/oauth/authorize`. The consent screen appears once per client; Passport auto-approves subsequent
authorize requests while an unrevoked, unexpired token exists for that client+user. That hits the Cloudflare OTP challenge, then Monica login
if not already signed in, then the Authorize/Deny screen. Approving redirects
back to claude.ai with a code that gets exchanged at `/oauth/token`.

## 6. Connecting Claude Code

Create a Sanctum personal access token in Monica: **Settings → API** → create
a token with read + write scope.

```bash
claude mcp add --transport http monica https://<hostname>/mcp \
  --header "Authorization: Bearer ${MONICA_TOKEN}" \
  --header "CF-Access-Client-Id: ${CF_ID}" \
  --header "CF-Access-Client-Secret: ${CF_SECRET}"
```

`CF_ID` / `CF_SECRET` are the service token from step 4a. Without them the
request never reaches Laravel — Cloudflare Access blocks it at the edge.

## 7. Security model

Layered: Cloudflare Access policies gate the edge, Laravel auth
(`auth:api,sanctum`) gates the app.

What the Bypass policy actually exposes: any request from
`160.79.104.0/21` reaches Laravel _without going through Cloudflare Access_
for exactly the five paths listed in step 4b — nothing else. It still needs a
valid Passport bearer token or Sanctum token to get past the app's own auth
middleware for `/mcp` itself; `/oauth/token` and `/oauth/register` are
designed to be reachable without a token (that's how OAuth issues the first
one).

Residual risks:

- A pre-auth vulnerability in Laravel, `laravel/mcp`, or Passport on the
  bypassed paths would be reachable from that IP range without Cloudflare's
  challenge as a second layer.
- A stolen bearer/Sanctum token grants whatever scope it carries (`read`,
  `write`) until revoked or expired.
- Prompt injection via note/contact content, combined with write-capable
  tools (`create-*`, `update-*`, `delete-*`), can cause an agent to make
  unwanted changes to vault data.

Mitigations:

- Keep `laravel/mcp`, `laravel/passport`, and Laravel core updated —
  `composer.lock` changes on these should not sit unreviewed.
- Revoke compromised tokens promptly: Sanctum tokens via **Settings → API**
  in Monica; Passport tokens currently only via the `oauth_access_tokens`
  table (no admin UI yet — `docker compose exec app php artisan tinker` or a
  direct `UPDATE ... SET revoked = 1`).
- Keep the mariadb volume backed up — a bad write via a compromised token is
  otherwise unrecoverable.
- `throttle:mcp` rate-limits POST /mcp, `/oauth/register`, and the `/.well-known`
  discovery routes to 60 requests/minute per authenticated user (or per IP if
  unauthenticated) — see `RateLimiter::for('mcp', ...)` in `AppServiceProvider`.
  Passport's `/oauth/token` uses Passport's own default throttle limiter;
  `/oauth/authorize` has no throttle.

## 8. Available tools (Phase 1)

| Tool              | Description                       |
| ----------------- | --------------------------------- |
| `list-vaults`     | List vaults the caller can access |
| `search-contacts` | Search contacts by name           |
| `get-contact`     | Fetch a single contact            |
| `create-contact`  | Create a contact                  |
| `update-contact`  | Update a contact                  |
| `delete-contact`  | Delete a contact                  |
| `list-notes`      | List notes for a contact          |
| `create-note`     | Create a note                     |
| `update-note`     | Update a note                     |
| `delete-note`     | Delete a note                     |
| `log-call`        | Log a call with a contact         |
| `list-calls`      | List calls for a contact          |
| `delete-call`     | Delete a call                     |
| `list-reminders`  | List reminders for a contact      |
| `create-reminder` | Create a reminder                 |
| `update-reminder` | Update a reminder                 |
| `delete-reminder` | Delete a reminder                 |
| `list-tasks`      | List tasks for a contact          |
| `create-task`     | Create a task                     |
| `toggle-task`     | Toggle a task's completed state   |
| `delete-task`     | Delete a task                     |

Notes:

- Reminders fan out to whatever notification channels are configured for the
  account (including Telegram) automatically — creating one via MCP triggers
  the same notifications as creating one in the UI.
- Logging a call as answered auto-creates a 90-day follow-up reminder (see
  `MonicaServer::$instructions` and the call-logging service).
- All 21 Phase 1 tools listed above are implemented and registered in
  `App\Mcp\Servers\MonicaServer::$tools` and active.

## 9. Troubleshooting

**401 from `/mcp`**
Bad or expired bearer token. Passport access tokens expire after 12 hours
(`Passport::tokensExpireIn`), refresh tokens after 30 days
(`Passport::refreshTokensExpireIn`) — claude.ai should refresh automatically;
if it doesn't, remove and re-add the connector. Sanctum tokens don't expire
by default; check it wasn't revoked in **Settings → API**.

**Cloudflare interstitial HTML instead of a JSON/MCP response**
The request never reached Laravel — it hit the Cloudflare Access login page.
Either the Access application's path scoping in step 4b is wrong (path not
included, or a more specific site-wide rule is matching first), or, for
Claude Code, the `CF-Access-Client-Id` / `CF-Access-Client-Secret` headers
are missing or wrong.

**OAuth flow fails at the discovery step**
claude.ai couldn't fetch `/.well-known/oauth-protected-resource` or
`/.well-known/oauth-authorization-server`. Confirm the scoped Access app from
step 4b actually includes both well-known paths (and their subpaths — some
clients probe path-suffixed variants), and that the Bypass policy's IP range
is current.

**Route/config drift after deploy**
Laravel caches routes and config in production. After pulling new code:

```bash
docker compose exec app php artisan route:clear
docker compose exec app php artisan config:clear
```

(or `route:cache` / `config:cache` if the deploy process expects cached
versions — check whatever `docker-entrypoint`/build step this image runs).
