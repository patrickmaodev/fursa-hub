# Fursa API (Phase 1)

Base URL: `http://localhost:8000` (or Vite proxy: same origin as frontend).

Auth: **Sanctum SPA** — session cookie + CSRF. Call `GET /sanctum/csrf-cookie` before `POST` login/register.

## Health

`GET /api/health`

```json
{ "status": "ok", "app": "Laravel" }
```

## Auth

### Register

`POST /api/register`

```json
{
  "name": "Jane Doe",
  "email": "jane@example.com",
  "password": "secret",
  "password_confirmation": "secret"
}
```

`201` — `User` object (see below). Session started.

### Login

`POST /api/login`

```json
{
  "email": "jane@example.com",
  "password": "secret",
  "remember": false
}
```

`200` — `User` object.

### Current user

`GET /api/user` — requires session.

`401` if unauthenticated.

### Logout

`POST /api/logout` — requires session.

```json
{ "message": "Logged out." }
```

## User JSON shape

```json
{
  "data": {
    "id": 1,
    "name": "Jane Doe",
    "email": "jane@example.com",
    "phone": null,
    "is_admin": false,
    "email_verified_at": null,
    "created_at": "...",
    "updated_at": "..."
  }
}
```

## Admin provisioning

There is no public admin registration. Create the first admin on the server:

```bash
php artisan admin:create
```

See [`ADMIN-PROVISIONING.md`](./ADMIN-PROVISIONING.md).

## Admin (Phase 1 stub)

`GET /api/admin/ping` — requires session + row in `admins` for the current user (`is_admin: true` in JSON).

```json
{ "message": "Admin access granted." }
```

## Errors

- `422` — validation (`message`, `errors`)
- `401` — unauthenticated
- `403` — forbidden (e.g. non-admin)

Future public endpoints: see `DOCUMENTATION.md` §16.
