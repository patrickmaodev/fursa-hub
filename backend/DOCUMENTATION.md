# Fursa Hub — architecture & product documentation

This document describes **what Fursa is** and **how we intend to build it**. It is not a live task list.

**Canonical references (keep in sync when architecture changes):**

| Document | Purpose |
|----------|---------|
| [`../ROADMAP.md`](../ROADMAP.md) | Phased delivery checklist |
| [`../docs/DATABASE-ERD.md`](../docs/DATABASE-ERD.md) | **Database schema & actors** (source of truth) |
| [`../docs/API.md`](../docs/API.md) | HTTP API (implemented + planned) |
| [`../docs/DESIGN-SYSTEM.md`](../docs/DESIGN-SYSTEM.md) | Vue UI (Kilimanjaro + Tailwind v4) |
| [`../docs/ADMIN-PROVISIONING.md`](../docs/ADMIN-PROVISIONING.md) | `php artisan admin:create` |

## Current implementation status (Phase 1 — foundation)

**Do not start Phase 2 (opportunities domain) until Phase 1 is stable** — auth, admin provisioning, separate user/admin UX, and docs aligned with code.

| Area | Status |
|------|--------|
| Repo layout | `backend/` (Laravel API), `frontend/` (Vue 3 SPA) |
| Auth | Laravel **Sanctum SPA** (session cookie + CSRF), `POST /api/register`, `/api/login`, `/api/logout`, `GET /api/user` |
| Identity | `users` table (login, profile); optional `phone` |
| Platform admin | `admins` table (`user_id` → `users`); **not** `users.role` / public admin signup |
| Admin provision | `php artisan admin:create` from `ADMIN_*` in `.env` |
| User routes (Vue) | `/login`, `/register`, `/dashboard` |
| Admin routes (Vue) | `/admin/login`, `/admin/dashboard` (no admin register) |
| API protection | `admin` middleware on `/api/admin/*` (requires `admins` row) |
| Design system | Kilimanjaro tokens, shared `App*` components on Vue |
| Local dev | MySQL + `php artisan serve` + `npm run dev` (Vite proxies `/api` and `/sanctum`) |
| **Phase 2** | **Not started** — no `categories`, `organizations`, `opportunities` migrations yet |

---

# 1. What is the project about?

Your project, which we can call **Fursa / Tanzania Opportunities**, is a platform that helps people **discover and access opportunities in one place**.

The platform should collect, organize, search, filter, and present opportunities from different organizations and sources.

### Main opportunity types

```text
FURSA
│
├── Jobs
├── Internships
├── Scholarships
├── Fellowships
├── Grants
├── Tenders
├── Training & Courses
├── Competitions
├── Volunteering
└── Other Opportunities
```

The important idea is:

> **Fursa does not necessarily provide the opportunity itself. It discovers and organizes opportunities and directs users to the original application/source.**

For example:

```text
Company X
     │
     │ publishes
     ▼
Software Developer Job
     │
     ▼
Fursa
     │
     ├── Title
     ├── Company
     ├── Location
     ├── Description
     ├── Requirements
     ├── Deadline
     └── Apply URL
              │
              ▼
        Original website
```

---

# 2. The problem you're solving

Today, opportunities are scattered across:

* Company websites
* LinkedIn
* Government websites
* NGO websites
* University websites
* Scholarship portals
* Social media
* Job boards
* Organization mailing lists

Someone looking for opportunities has to search many places.

Your application becomes a **central discovery platform**.

```text
       MANY SOURCES
            │
     ┌──────┼──────┐
     ▼      ▼      ▼
 Companies NGOs Universities
     │      │      │
     └──────┼──────┘
            ▼
         FURSA
            │
     ┌──────┼──────┐
     ▼      ▼      ▼
   Jobs  Scholarships Grants
     │      │      │
     └──────┼──────┘
            ▼
          USERS
```

---

# 3. High-level architecture

Since you already installed Laravel and Vue, I recommend this architecture:

```text
                    ┌─────────────────────┐
                    │       USER          │
                    │ Browser / Mobile    │
                    └──────────┬──────────┘
                               │
                               ▼
                    ┌─────────────────────┐
                    │       VUE 3         │
                    │     Frontend        │
                    └──────────┬──────────┘
                               │
                         REST API / JSON
                               │
                               ▼
                    ┌─────────────────────┐
                    │      LARAVEL        │
                    │       API           │
                    └──────────┬──────────┘
                               │
             ┌─────────────────┼─────────────────┐
             │                 │                 │
             ▼                 ▼                 ▼
        ┌─────────┐       ┌──────────┐      ┌─────────┐
        │  MySQL  │       │  Redis   │      │ Storage │
        │ Database│       │ Cache/Job│      │         │
        └─────────┘       └──────────┘      └─────────┘
                               │
                               ▼
                    ┌─────────────────────┐
                    │ Opportunity Sources │
                    ├─────────────────────┤
                    │ APIs                │
                    │ Websites            │
                    │ Admin submissions   │
                    │ Future scrapers     │
                    └─────────────────────┘
```

---

# 4. Separate the system into two major applications

You already have:

### Backend

```text
Laravel
```

Responsible for:

* Authentication
* Users
* Opportunities
* Categories
* Organizations
* API
* Search/filtering
* Bookmarks
* Applications
* Notifications
* Admin operations
* Opportunity expiry
* Data collection
* Scheduled jobs

### Frontend

```text
Vue
```

Responsible for:

* Homepage
* Opportunity listing
* Search
* Filters
* Opportunity details
* User dashboard
* Saved opportunities
* Profile
* Admin UI

---

# 5. Core database architecture

Don't start with dozens of tables — but **model the real domain**: people, platform admins, organizations, and opportunities are different concepts.

**Full ERD:** [`../docs/DATABASE-ERD.md`](../docs/DATABASE-ERD.md).

### Actors (conceptual)

```text
                         FURSA HUB
                             │
         ┌───────────────────┼───────────────────┐
         │                   │                   │
    admins (staff)         users            organizations
    via admins table    (identity)         (publishers)
         │                   │                   │
         │                   ├── seeker          ├── opportunities
         │                   ├── bookmarks       └── organization_users
         │                   └── applications         (members)
         └── moderate
```

- **Seeker** — a `users` row (browse, bookmark, track applications).
- **Platform administrator** — `users` row **plus** `admins.user_id` (moderate platform; provisioned via Artisan, not public registration).
- **Organization member** — `organization_users` linking `users` ↔ `organizations` (Phase 2+ portal).
- **Tender / job / scholarship** — same **`opportunities`** table; **category** = type (e.g. Tenders). No separate `tenders` table unless tender-only fields later justify `tender_details`.

### Implemented today (Phase 1)

```text
users
admins          → user_id (unique)
sessions
(personal_access_tokens — Sanctum)
```

### Planned — Phase 2+ (not migrated yet)

```text
categories
organizations
organization_users
opportunities
bookmarks
applications
```

### Later

```text
opportunity_sources
tags / opportunity_tags
notifications
subscriptions
reports
opportunity_views
```

Organization legal fields (TIN, registration number, address, etc.) live on **`organizations`**, not on `users`.

---

# 6. Opportunity is the central entity

Your most important model is:

```text
Opportunity
```

Something like:

```text
opportunities
-------------------------
id
organization_id
category_id
title
slug
description
requirements
location
employment_type
deadline
source_url
application_url
image
status
published_at
created_at
updated_at
```

But don't blindly create all of these immediately. We can refine the schema after defining the requirements.

---

# 7. Categories

Instead of hardcoding:

```php
if ($type === 'job') ...
```

use a category system.

Example:

```text
categories

1  Jobs
2  Internships
3  Scholarships
4  Fellowships
5  Grants
6  Tenders
7  Training
8  Competitions
9  Volunteering
```

This makes the system expandable.

Later you can add:

```text
Remote Jobs
Research Opportunities
Startup Opportunities
Youth Opportunities
Women Opportunities
Technology Opportunities
```

without changing your database architecture.

---

# 8. Organizations

An opportunity belongs to an **organization** (Phase 2). Company/TIN/registration data stays on `organizations`, not on `users`.

For example:

```text
Organization
      │
      ├── Name, slug
      ├── Registration number, TIN
      ├── Description, address
      ├── Website, email, phone
      └── Members (organization_users: owner, manager, editor, …)
             │
             ▼
       Opportunities
```

Example:

```text
Vodacom Tanzania
       │
       ├── Software Engineer
       ├── Network Engineer
       └── Graduate Internship
```

This allows users to view:

> **All opportunities from this organization**

---

# 9. User side

A visitor should be able to browse without creating an account.

### Public

```text
/
├── Home
├── Opportunities
│   ├── Jobs
│   ├── Scholarships
│   ├── Internships
│   └── Grants
├── Opportunity Details
├── Organizations
└── Search
```

### Authenticated user (seeker)

```text
/login
/register
/dashboard
├── Profile (future)
├── Saved Opportunities (Phase 4)
├── Applications (Phase 4)
├── Notifications (later)
└── Preferences (later)
```

### Staff / platform admin (separate UX, same `users` login API)

```text
/admin/login          ← no register
/admin/dashboard
/admin/opportunities  (Phase 5)
...
```

**Important:** Separate URLs improve clarity; **security** still requires authentication + an `admins` row (and API `admin` middleware). Visiting `/admin/dashboard` without privileges must redirect or deny.

Public browsing of opportunities (Phase 2) should not require registration to **read** a listing.

---

# 10. Admin side

You need a strong admin system because opportunity data needs moderation.

**Provisioning:** first admin via `php artisan admin:create` (`ADMIN_*` in `.env`). See [`../docs/ADMIN-PROVISIONING.md`](../docs/ADMIN-PROVISIONING.md). No public “register as admin”.

**Sign-in:** Vue `/admin/login` → same Sanctum session as users, but only accounts with an `admins` record may use admin routes.

```text
/admin
│
├── login             (implemented: /admin/login)
├── dashboard         (implemented: /admin/dashboard)
│
├── Dashboard
│
├── Opportunities
│   ├── All
│   ├── Pending
│   ├── Published
│   ├── Expired
│   └── Rejected
│
├── Categories
│
├── Organizations
│
├── Users
│
├── Sources
│
└── Reports
```

The admin should be able to:

```text
Create
Edit
Publish
Unpublish
Reject
Delete
Expire
Feature
```

---

# 11. Opportunity lifecycle

This is an important part of the architecture.

An opportunity shouldn't simply be:

```text
created → visible forever
```

Instead:

```text
                 ┌─────────────┐
                 │   DRAFT     │
                 └──────┬──────┘
                        │
                     review
                        │
                        ▼
                 ┌─────────────┐
                 │   PENDING   │
                 └──────┬──────┘
                        │
                     approve
                        │
                        ▼
                 ┌─────────────┐
                 │  PUBLISHED  │
                 └──────┬──────┘
                        │
                  deadline passed
                        │
                        ▼
                 ┌─────────────┐
                 │   EXPIRED   │
                 └─────────────┘
```

This becomes very important once you have thousands of opportunities.

---

# 12. How opportunities enter the system

This is where your project can become very interesting.

Initially:

```text
                    ┌──────────────┐
                    │     ADMIN    │
                    └──────┬───────┘
                           │
                           ▼
                    Create Opportunity
                           │
                           ▼
                       Laravel
                           │
                           ▼
                         MySQL
```

Later:

```text
                  OPPORTUNITY SOURCES
                         │
          ┌──────────────┼──────────────┐
          ▼              ▼              ▼
        APIs          Websites       Partners
          │              │              │
          └──────────────┼──────────────┘
                         ▼
                  Collection System
                         │
                         ▼
                    Normalization
                         │
                         ▼
                    Deduplication
                         │
                         ▼
                    Admin Review
                         │
                         ▼
                     Published
```

**Don't build scraping first.**

Build the platform first.

Once the core system works, automate data collection.

---

# 13. Search architecture

Initially:

```text
Vue
 │
 │ GET /api/opportunities?search=software
 ▼
Laravel
 │
 ▼
MySQL
```

Search by:

```text
keyword
category
location
deadline
organization
opportunity type
```

Later, when your database becomes large:

```text
Vue
 │
 ▼
Laravel
 │
 ▼
Meilisearch
 │
 ▼
Results
```

Don't introduce Elasticsearch on day one.

---

# 14. Notifications

Eventually users could say:

> Notify me when a Software Developer job is published in Dar es Salaam.

Architecture:

```text
User
 │
 ▼
Preferences
 │
 ├── Category: Jobs
 ├── Keyword: Software Developer
 ├── Location: Dar es Salaam
 └── Frequency: Daily
             │
             ▼
          Laravel
             │
          Scheduler
             │
             ▼
       Matching Jobs
             │
             ▼
        Notification
```

Channels can later include:

```text
Email
WhatsApp
SMS
Push notification
```

---

# 15. Recommended project structure

### Laravel (`backend/`)

```text
backend/
├── app/
│   ├── Models/          (User, Admin, …)
│   ├── Http/
│   │   ├── Controllers/Api/
│   │   ├── Requests/
│   │   ├── Resources/
│   │   └── Middleware/  (EnsureUserIsAdmin)
│   └── Console/Commands/ (CreateAdmin → admin:create)
├── config/              (admin.php, sanctum.php, cors.php)
├── database/
├── routes/api.php
└── tests/
```

### Vue (`frontend/`)

```text
frontend/
├── src/
│   ├── components/      (AppButton, AppInput, AppCard, …)
│   ├── layouts/         (AppLayout, AdminLayout)
│   ├── views/           (+ views/admin/)
│   ├── router/
│   ├── stores/          (auth)
│   ├── services/        (api.js — Sanctum + CSRF)
│   └── assets/app.css   (Tailwind v4 + Kilimanjaro tokens)
└── vite.config.js       (proxy /api, /sanctum → Laravel)
```

Laravel’s own Tailwind bundle (`backend/resources/css`) is for Blade/Vite defaults — **the public SPA uses `frontend/` styling** ([`../docs/DESIGN-SYSTEM.md`](../docs/DESIGN-SYSTEM.md)).

---

# 16. API architecture

Keep Laravel as a **JSON API** for the Vue SPA. Auth: **Sanctum SPA** — browser calls `GET /sanctum/csrf-cookie` before `POST` login/register; cookies + CSRF on mutating requests.

**Detail:** [`../docs/API.md`](../docs/API.md).

### Implemented (Phase 1)

```text
GET    /api/health
GET    /sanctum/csrf-cookie

POST   /api/register      → users only (no admins row)
POST   /api/login
POST   /api/logout
GET    /api/user          → includes is_admin (from admins)

GET    /api/admin/ping    → admin middleware
```

### Planned (Phase 2+)

```text
GET    /api/opportunities
GET    /api/opportunities/{slug}
GET    /api/categories
GET    /api/organizations
…
GET    /api/bookmarks
POST   /api/admin/opportunities
…
```

---

# 17. Local development

Run services directly on the host (no container stack in this repo):

```text
MySQL          → backend .env (DB_*)
php artisan serve → http://localhost:8000
npm run dev    → http://localhost:5173 (frontend/vite.config.js proxies API)
```

Production hosting (VPS, Laravel Cloud, etc.) is chosen later and is out of scope for Phase 1.

---

# 18. Development roadmap

Build in phases. **Live checklist:** [`../ROADMAP.md`](../ROADMAP.md).

Do **not** skip ahead to Phase 2 until Phase 1 is verified end-to-end (register, login, admin create, admin login, middleware, docs).

I would **not** start with scraping, AI, notifications, or advanced search.

### Phase 1 — Foundation (current focus)

```text
✓ Laravel API + routes/api.php
✓ Vue application (frontend/)
✓ Database (users, admins, sessions)
✓ Sanctum SPA authentication
✓ User registration + login
✓ Admin provisioning (admin:create)
✓ Separate /login vs /admin/login (Vue)
✓ Kilimanjaro design system (Vue)
```

### Phase 2 — Core opportunities (not started — do not jump here yet)

```text
○ Categories, organizations, opportunities (migrations per DATABASE-ERD.md)
○ Seeders
○ Public listing + detail API + Vue pages
```

### Phase 3 — Search & discovery

```text
○ Search, filters, pagination, sorting
○ Expire opportunities past deadline
```

### Phase 4 — User features

```text
○ Bookmarks, application tracking, profile
```

### Phase 5 — Admin product UI

```text
○ Moderation queues, CRUD, publish/reject/feature
```

### Phase 6 — Automation

```text
○ Sources, queues, deduplication, scheduled expiry
```

### Phase 7 — Intelligence

Later: optional Python/NLP/recommendations.

---

# 19. The most important architectural decision

I would design the project around this principle:

> **Laravel is the source of truth. Vue is the presentation layer. External websites/APIs are opportunity sources, not your database.**

So:

```text
              External Internet
                     │
            ┌────────┴────────┐
            │                 │
          APIs             Websites
            │                 │
            └────────┬────────┘
                     ▼
              Collection Layer
                     │
                     ▼
               Laravel API
                     │
              ┌──────┴──────┐
              ▼             ▼
           MySQL          Redis
              │
              ▼
           Laravel API
              │
              ▼
             Vue
              │
              ▼
            USER
```

That architecture gives you room to grow from a **simple opportunity listing website** into a proper **opportunity aggregation platform**.

## What to do next (stay on Phase 1 until complete)

Phase 0/1 design artifacts exist (ERD, API sketch, design system, admin provisioning). **Do not open Phase 2 migrations** until the items below are satisfied:

1. **Phase 1 checkpoint** — user register/login/logout; `admin:create`; `/admin/login` + `/api/admin/ping`; guards and middleware behave correctly.
2. **Hardening** — PHPUnit for auth and `admin:create` (run locally); `.env.example` documented; no secrets committed.

When Phase 1 is signed off, start Phase 2 strictly from [`../docs/DATABASE-ERD.md`](../docs/DATABASE-ERD.md): `categories` → `organizations` → `opportunities`, then public API and Vue listing pages — tracked in [`../ROADMAP.md`](../ROADMAP.md).
