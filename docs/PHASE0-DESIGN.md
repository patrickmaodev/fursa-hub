# Phase 0 — Design snapshot

**Canonical schema:** [`DATABASE-ERD.md`](./DATABASE-ERD.md) (actors, tables, phases, flows).

## Quick reference

- **Identity:** `users` (login, profile)
- **Platform admin:** `admins` (`user_id`, not `users.role`)
- **Publishers:** `organizations` + `organization_users`
- **Listings:** `opportunities` → `categories` (Tender is a category, not a separate table)
- **Seekers:** `users` → `bookmarks`, `applications`

## Opportunity status

`draft` | `pending` | `published` | `expired` | `rejected`

## Vue routes

| Path | Access |
|------|--------|
| `/`, `/opportunities` (Phase 2) | Public |
| `/login`, `/register` | Guest users |
| `/dashboard` | Authenticated user |
| `/admin/login` | Admin sign-in |
| `/admin/dashboard` | User with `admins` row |

## API

See [`API.md`](./API.md). Admin provisioning: [`ADMIN-PROVISIONING.md`](./ADMIN-PROVISIONING.md).
