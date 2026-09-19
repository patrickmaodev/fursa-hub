# Fursa Hub — Development roadmap

Step-by-step plan for building **Fursa** (opportunity discovery platform). Architecture and product context live in [`backend/DOCUMENTATION.md`](backend/DOCUMENTATION.md).

**Stack:** Laravel API (`backend/`) · Vue 3 SPA (`frontend/`) · MySQL · Redis (later)  
**Auth (locked):** [Laravel Sanctum](https://laravel.com/docs/sanctum) **SPA authentication** (session cookie + CSRF), not API tokens.

---

## Current state

| Area | Status |
|------|--------|
| Laravel | Sanctum SPA, `users` + `admins` tables — see `docs/DATABASE-ERD.md` |
| Vue | Kilimanjaro + Tailwind v4, shared UI components, auth pages — see `docs/DESIGN-SYSTEM.md` |
| Local dev | `php artisan serve` (backend) + `npm run dev` (frontend), MySQL |
| Domain (opportunities, categories, orgs) | Not started |

Treat checkmarks in `DOCUMENTATION.md` as **goals**, not completed work.

---

## How we work

1. **One vertical slice** — migration → model → API → minimal UI → verify, then next slice.
2. **API before polish** — agree on JSON shape; wire Vue with real data early.
3. **Seeders from day one** — categories, orgs, sample opportunities so the UI is never empty.
4. **Defer** scraping, Meilisearch, WhatsApp/SMS, and heavy automation until public listing + admin moderation work end-to-end.

---

## Authentication: Sanctum SPA (cookie)

### Why this choice

Vue and Laravel run on **different origins in development** (e.g. `http://localhost:5173` and `http://localhost:8000`). Sanctum’s SPA mode uses the **web session** (HTTP-only cookies) with CSRF protection—good fit for a first-party browser app without storing tokens in `localStorage`.

### Request flow (every session)

```text
Vue (frontend origin)
  │
  ├─► GET  /sanctum/csrf-cookie     (Laravel sets XSRF-TOKEN cookie)
  │
  ├─► POST /api/register|login      (withCredentials; sends CSRF header)
  │
  └─► GET  /api/user, POST /api/logout, protected routes
        (session cookie + CSRF on mutating requests)
```

### Backend checklist (Phase 1)

- [ ] `composer require laravel/sanctum`
- [ ] Publish/migrate Sanctum (`personal_access_tokens` — keep for possible future; SPA mode does not require storing tokens in the frontend)
- [ ] `bootstrap/app.php` — register `api` routes; middleware: `api` group uses `EnsureFrontendRequestsAreStateful` (Sanctum) where documented for your Laravel version
- [ ] `config/sanctum.php` — `stateful` domains include local frontend host(s)
- [ ] `config/session.php` + `.env`:
  - `SESSION_DRIVER=database` (or `redis` later)
  - `SESSION_DOMAIN` — often `localhost` for local dev (avoid breaking cross-port cookies)
  - `SANCTUM_STATEFUL_DOMAINS=localhost,localhost:5173,127.0.0.1,127.0.0.1:5173`
- [ ] `config/cors.php` — allow frontend origin; `supports_credentials` => **true**
- [ ] Routes under `routes/api.php` with `auth:sanctum` for protected endpoints
- [ ] Login/register use **session** (`Auth::attempt`, regenerate session); logout invalidates session

### Frontend checklist (Phase 1)

- [ ] `axios` (or `fetch` wrapper) with `withCredentials: true`
- [ ] Base URL → Laravel API (e.g. `VITE_API_URL=http://localhost:8000`)
- [ ] Before login/register: call `/sanctum/csrf-cookie`
- [ ] Send `X-XSRF-TOKEN` from cookie on POST/PUT/PATCH/DELETE (axios can read `XSRF-TOKEN` cookie when configured)
- [ ] Auth store: `fetchUser()` on app load; route guards for `/dashboard` and `/admin`
- [ ] Vite dev server proxy (optional): proxy `/api` and `/sanctum` to Laravel to avoid CORS during dev—document whichever approach you use in `backend/.env.example` + `frontend/.env.example`

### Production notes (later)

- Same **site** or explicit stateful domains (e.g. `fursa.example.com` for both SPA and API, or `app.` + `api.` with correct `SANCTUM_STATEFUL_DOMAINS` and `SESSION_DOMAIN`)
- HTTPS only; `SESSION_SECURE_COOKIE=true`
- Do **not** put session secrets or Sanctum keys in the Vue bundle

---

## Phase 0 — Design (before feature code)

- [ ] **0.1** ERD: `users`, `categories`, `organizations`, `opportunities`, `bookmarks`, `applications`
- [ ] **0.2** Opportunity `status`: `draft` | `pending` | `published` | `expired` | `rejected` (+ optional `featured` flag)
- [ ] **0.3** Platform admin: `admins` table (`user_id`); gate `/api/admin/*` and `/admin` UI
- [ ] **0.4** API contract sketch (public, auth, bookmarks, admin)—extend doc section 16 or add `API.md`
- [ ] **0.5** Vue route map: public, auth, dashboard, admin
- [ ] **0.6** Env templates: `backend/.env.example`, `frontend/.env.example` (API URL, Sanctum/CORS-related vars)

**Exit criteria:** ERD + status/roles + route list agreed; no ambiguity before migrations.

---

## Phase 1 — Foundation

### Backend

- [ ] **1.1** `routes/api.php` + health/public stub
- [ ] **1.2** Sanctum SPA setup (see auth section above)
- [ ] **1.3** `POST /api/register`, `POST /api/login`, `POST /api/logout`, `GET /api/user`
- [ ] **1.4** Admin middleware + `php artisan admin:create`
- [ ] **1.5** Form requests + consistent JSON errors (`422` validation, `401`/`403` auth)

### Frontend

- [ ] **1.6** API client (credentials + CSRF)
- [ ] **1.7** Pinia auth store + login/register pages
- [ ] **1.8** App layout (header, nav, footer)
- [ ] **1.9** Route guards: guest vs authenticated vs admin (separate `/login` and `/admin/login`)

**Checkpoint:** Register → login → `GET /api/user` shows user → logout; session persists on refresh.

---

## Phase 2 — Core opportunities

### Data layer

- [ ] **2.1** Migrations: `categories`, `organizations`, `opportunities` (slug, URLs, deadline, status, `published_at`, FKs)
- [ ] **2.2** Models, relationships, factories
- [ ] **2.3** Seeders: 9 categories (per `DOCUMENTATION.md`), sample orgs, 10–20 opportunities (mixed statuses)

### Public API

- [ ] **2.4** `GET /api/categories`, `GET /api/categories/{slug}`
- [ ] **2.5** `GET /api/organizations`, `GET /api/organizations/{slug}` (+ opportunities)
- [ ] **2.6** `GET /api/opportunities` (published, sensible deadline rules)
- [ ] **2.7** `GET /api/opportunities/{slug}`
- [ ] **2.8** API resources/transformers for stable JSON

### Public Vue

- [ ] **2.9** Home (latest/featured + category links)
- [ ] **2.10** Opportunity list
- [ ] **2.11** Opportunity detail + external **Apply** (`application_url`)
- [ ] **2.12** Organization page

**Checkpoint:** Anonymous user browses and opens apply link; no login required to read.

---

## Phase 3 — Search and discovery

- [ ] **3.1** Query params: `search`, `category`, `location`, `deadline_before`, `sort`, `page`
- [ ] **3.2** Filter UI + pagination
- [ ] **3.3** Scheduled command: expire opportunities past `deadline`

**Checkpoint:** Filtered search with pagination works on real seed data.

---

## Phase 4 — User features

- [ ] **4.1** Migrations: `bookmarks`, `applications`
- [ ] **4.2** `GET /api/bookmarks`, `POST/DELETE /api/opportunities/{id}/bookmark` (auth:sanctum)
- [ ] **4.3** Application tracking API (user-owned records)
- [ ] **4.4** Dashboard: saved, applications, profile
- [ ] **4.5** (Optional) `opportunity_views` for analytics

**Checkpoint:** Logged-in user saves opportunities and tracks applications.

---

## Phase 5 — Admin

- [ ] **5.1** `POST/PUT/DELETE /api/admin/opportunities` (+ categories, organizations)
- [ ] **5.2** Actions: publish, unpublish, reject, expire, feature
- [ ] **5.3** Admin UI: pending queue, published/expired lists
- [ ] **5.4** User management (minimal: list, optional deactivate)

**Checkpoint:** Admin draft → publish → visible on public site.

---

## Phase 6 — Automation (later)

- [ ] **6.1** `opportunity_sources` + manual import (URL/CSV)
- [ ] **6.2** Queue worker + Redis
- [ ] **6.3** Deduplication (e.g. same `application_url`)
- [ ] **6.4** External APIs / scrapers (last)

---

## Phase 7 — Intelligence (much later)

- [ ] Saved-search notifications (email first)
- [ ] Meilisearch if MySQL search is insufficient
- [ ] Optional Python sidecar (NLP, recommendations)

---

## Suggested next three tasks

1. Complete **Phase 0.1–0.3** (ERD + status enum + admin role)—short spec in repo or comment in a migration plan.
2. Implement **Phase 1.1–1.3** (API routes + Sanctum SPA + register/login/user).
3. Implement **Phase 2.1–2.3** (domain migrations + seeders).

---

## Progress log

| Date | Done |
|------|------|
| 2026-09-19 | Roadmap created; auth decision: **Sanctum SPA cookie** |
| 2026-09-19 | **Phase 0 + Phase 1** implemented (see `docs/PHASE0-DESIGN.md`, `docs/API.md`) |

Update this table as phases complete.
