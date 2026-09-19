# Administrator provisioning

Fursa does **not** expose a public “register as admin” flow. The first administrator is created on the server from environment variables.

## Setup

Add to `backend/.env`:

```env
ADMIN_NAME="System Administrator"
ADMIN_EMAIL=admin@fursahub.co.tz
ADMIN_PASSWORD=change-this-password
ADMIN_ROLE=admin
```

Run:

```bash
cd backend
php artisan admin:create
```

The command:

- Requires `ADMIN_ROLE=admin`
- Creates a **`users`** row (if needed) and an **`admins`** row linked by `user_id`
- Hashes `ADMIN_PASSWORD` with Laravel’s `Hash` facade — never stores plain text in the database
- **Skips creation** if a user with `ADMIN_EMAIL` already exists (no duplicate row)

Regular users register at `/register` (`POST /api/register` — no `admins` row). Admins sign in at **`/admin/login`**. Same Sanctum session and `users` table; platform privileges come from the **`admins`** table. See [`DATABASE-ERD.md`](./DATABASE-ERD.md).

## Production

1. Use a strong `ADMIN_PASSWORD` only for the first run.
2. After `admin:create` succeeds, **remove `ADMIN_PASSWORD` from `.env`** (or move secrets to your host’s secret manager).
3. Rotate the admin password through a future “change password” flow or a one-off `php artisan tinker` update if needed.

## Local development

You can keep temporary credentials in `.env` for convenience, but avoid committing real passwords. Prefer `admin@fursahub.co.tz` + a local-only password, then log in through the Vue app.
