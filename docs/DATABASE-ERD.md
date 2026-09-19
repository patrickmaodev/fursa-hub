# Fursa Hub — database domain model

This document is the **source of truth** for schema design before Phase 2 opportunity migrations. It separates **authentication identity** (`users`) from **platform roles** (`admins`, `organization_users`) and **business entities** (`organizations`, `opportunities`).

Product context: [`backend/DOCUMENTATION.md`](../backend/DOCUMENTATION.md).

---

## Design principles

1. **One person, one login** — `users` holds credentials and profile fields shared by seekers, org members, and admins.
2. **Admins are not a different login table** — `admins` links a `user_id` to platform moderation privileges (optional extra admin metadata later).
3. **Organizations own opportunities** — company/TIN/address live on `organizations`, not on `users` or duplicated on every opportunity row.
4. **Tender = opportunity type** — use `categories` (e.g. Tenders, Jobs), not a separate `tenders` table unless tender-specific fields justify `tender_details` later.
5. **Normalize organization data once** — `organization_id` on `opportunities`, not `company_name` columns on each listing.
6. **Don’t over-build** — add `tags`, `sources`, `reports` when features need them; keep Phase 2 to the core graph below.

---

## Actor model

```text
                    FURSA HUB (platform)
                           │
         ┌─────────────────┼─────────────────┐
         │                 │                 │
    PLATFORM           PEOPLE           ORGANIZATIONS
     (admins)          (users)          (publishers)
         │                 │                 │
         │                 ├── seeker        ├── jobs, tenders, …
         │                 ├── bookmark      └── org members
         │                 └── apply              (organization_users)
         │
         └── moderate / configure
```

| Concept | Table(s) | Description |
|---------|----------|-------------|
| Person (identity) | `users` | Login, name, email, phone, password |
| Platform administrator | `admins` | `user_id` → Fursa staff who moderate the platform |
| Organization | `organizations` | Legal/commercial entity publishing opportunities |
| Org member | `organization_users` | User ↔ org with role (`owner`, `manager`, `editor`, …) |
| Opportunity seeker | `users` (no extra table) | Discovers, bookmarks, applies |
| Opportunity | `opportunities` | Listing owned by an `organization`, typed by `category` |

A **construction company bidding on a tender** is an `organization` (member users via `organization_users`), not a stretched `users` row with company fields.

---

## Entity relationship (core)

```mermaid
erDiagram
    users ||--o| admins : "may be"
    users ||--o{ organization_users : "belongs to"
    organizations ||--o{ organization_users : "has members"
    organizations ||--o{ opportunities : "publishes"
    categories ||--o{ opportunities : "classifies"
    users ||--o{ bookmarks : "saves"
    users ||--o{ applications : "submits"
    opportunities ||--o{ bookmarks : "saved in"
    opportunities ||--o{ applications : "receives"
    opportunity_sources ||--o{ opportunities : "ingested from"

    users {
        bigint id PK
        string name
        string email UK
        string phone nullable
        string password
        timestamp email_verified_at nullable
        timestamps created_at updated_at
    }

    admins {
        bigint id PK
        bigint user_id FK UK
        timestamps created_at updated_at
    }

    organizations {
        bigint id PK
        string name
        string slug UK
        string registration_number nullable
        string tin nullable
        text description nullable
        string address nullable
        string website nullable
        string email nullable
        string phone nullable
        timestamps created_at updated_at
    }

    organization_users {
        bigint id PK
        bigint organization_id FK
        bigint user_id FK
        string role
        timestamps created_at updated_at
    }

    categories {
        bigint id PK
        string name
        string slug UK
        timestamps created_at updated_at
    }

    opportunities {
        bigint id PK
        bigint organization_id FK
        bigint category_id FK
        string title
        string slug UK
        text description nullable
        text requirements nullable
        string location nullable
        string employment_type nullable
        timestamp deadline nullable
        string source_url nullable
        string application_url nullable
        string status
        boolean is_featured default false
        timestamp published_at nullable
        timestamps created_at updated_at
    }

    bookmarks {
        bigint id PK
        bigint user_id FK
        bigint opportunity_id FK
        timestamps created_at updated_at
    }

    applications {
        bigint id PK
        bigint user_id FK
        bigint opportunity_id FK
        string status
        text notes nullable
        timestamps created_at updated_at
    }
```

---

## Opportunity lifecycle (`status`)

| Status | Meaning |
|--------|---------|
| `draft` | Not submitted |
| `pending` | Awaiting admin review |
| `published` | Public |
| `expired` | Past deadline or manual expiry |
| `rejected` | Not approved |

---

## Categories (initial seed)

Jobs, Internships, Scholarships, Fellowships, Grants, **Tenders**, Training, Competitions, Volunteering, Other.

Tenders share the same `opportunities` structure; category = Tender.

---

## Organization member roles (`organization_users.role`)

Suggested enum (string): `owner`, `manager`, `editor`, `viewer`.

Phase 2 can ship with table + `owner` only; expand when org self-service is built.

---

## Flows

### Publish (future org portal)

```text
Organization → organization_users (editor+) → creates opportunity → pending → admin publishes
```

### Seeker (Phase 2 public + Phase 4 user features)

```text
User (guest or registered) → browses opportunities → application_url (external)
Registered user → bookmarks / application tracking (applications table)
```

### Admin provision (implemented)

```text
.env (ADMIN_*) → php artisan admin:create → users + admins
```

### Admin sign-in (implemented)

```text
/admin/login → Sanctum session → admins row must exist for user
```

---

## Tables by phase

| Phase | Tables |
|-------|--------|
| **1 (done)** | `users`, `sessions`, `admins`, Sanctum tokens |
| **2** | `categories`, `organizations`, `opportunities` (+ seeders) |
| **4** | `bookmarks`, `applications` |
| **5** | Admin CRUD on opportunities/orgs (no new core tables) |
| **6+** | `opportunity_sources`, `tags`, `opportunity_tags`, `reports`, `opportunity_views` |

Optional later: `tender_details` (1:1 `opportunities`) if tender-specific fields diverge.

---

## What we are **not** doing

- Putting `organization_name`, TIN, or registration on `users`.
- A separate `tenders` table parallel to `opportunities` (use category).
- A separate `admins` login table (email/password stay on `users`).
- Storing `role = admin` on `users` (use `admins`).

---

## Implementation status

| Item | Status |
|------|--------|
| `users` | Migrated |
| `admins` | Migrated (replaces `users.role`) |
| `organizations`, `opportunities`, … | Documented; migrations in Phase 2 |

See also: [`PHASE0-DESIGN.md`](./PHASE0-DESIGN.md), [`ADMIN-PROVISIONING.md`](./ADMIN-PROVISIONING.md).
