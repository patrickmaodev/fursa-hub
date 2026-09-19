# Fursa design system — Kilimanjaro

Visual identity for the **Vue frontend** (`frontend/`). Backend Laravel UI should reuse the same token hex values when admin screens are built.

**Principles**

- Trustworthy, professional, accessible, modern — not generic startup neon or heavy “government portal” decoration.
- Tanzanian inspiration lives in **color and tone**, not flags, patterns, or stock imagery on every page.
- **Teal** = primary actions (Apply, View opportunity, Continue, Publish).
- **Gold** = opportunity highlights (Featured, important callouts) — not default CTAs.
- **Green** = success states only.
- **Orange/warning** = deadlines and caution.
- **Red** = errors and destructive actions.

---

## Color tokens

Defined in `frontend/src/assets/app.css` as `--fursa-*` CSS variables (switch automatically in dark mode via `prefers-color-scheme: dark`).

### Light

| Token | Hex | Usage |
|-------|-----|--------|
| Primary | `#0F4C5C` | Primary buttons, key links |
| Primary hover | `#0A3A47` | Primary hover |
| Secondary | `#3D5A80` | Secondary actions, org names, info emphasis |
| Accent | `#E8A838` | Featured badges, highlights |
| Background | `#F7F9FA` | Page background |
| Surface | `#FFFFFF` | Cards, header, inputs |
| Text | `#1A2332` | Body and headings |
| Muted | `#5C6B7A` | Meta, hints, footer |
| Border | `#DDE3E8` | Dividers, input borders |
| Success | `#0D7A4E` | Success alerts, success badges |
| Warning | `#B45309` | Deadlines, warnings |
| Danger | `#B42318` | Errors, destructive |

### Dark

| Token | Hex |
|-------|-----|
| Primary | `#5BA8B8` |
| Primary hover | `#7BC4D4` |
| Secondary | `#94A8C4` |
| Accent | `#F0C060` |
| Background | `#0F1419` |
| Surface | `#1A2229` |
| Text | `#E8ECF0` |
| Muted | `#9AA8B4` |
| Border | `#2D3844` |
| Success | `#34D399` |
| Warning | `#FBBF24` |
| Danger | `#F87171` |

### Tailwind utilities

Mapped in `@theme`: `bg-primary`, `text-muted`, `border-border`, `bg-surface`, `bg-accent`, `text-warning`, `text-danger`, `text-success`, etc.

---

## Typography

| Role | Spec |
|------|------|
| Font | **Plus Jakarta Sans** (`@fontsource/plus-jakarta-sans`) |
| Body | 15px, line-height ~1.6 |
| H1 | `text-3xl` / bold — page titles |
| H2 | `text-2xl` / semibold — sections |
| H3 | `text-lg` / semibold — card titles |
| Small / meta | `text-sm` + `text-muted` |

---

## Spacing and layout

| Pattern | Value |
|---------|--------|
| Max content width | `max-w-5xl` (layout main) |
| Auth forms | `max-w-md` |
| Page padding | `px-4 py-8` (sm: `px-6 py-10`) |
| Card padding | `p-5` default (`AppCard` `md`) |
| Section gap | `mt-8` / `space-y-4` typical |

Border radius: `rounded-md` (buttons, inputs), `rounded-lg` (cards, alerts).

---

## Components (`frontend/src/components/`)

Import and compose these instead of one-off scoped form CSS.

### `AppButton`

| Prop | Values |
|------|--------|
| `variant` | `primary` (teal), `secondary`, `outline` (Save / neutral), `ghost`, `danger`, `accent` (gold — rare) |
| `size` | `sm`, `md`, `lg` |
| `to` | Vue Router path → renders `RouterLink` |

**Hierarchy:** Apply / View opportunity → `primary`. Save → `outline`. Destructive → `danger`. Featured promos only → `accent`.

### `AppInput`

Label, `v-model`, `error`, optional `hint`, standard focus ring (`ring-primary/20`).

### `AppCard`

`padding`: `none` | `sm` | `md` | `lg`. `hoverable` for listing rows.

### `AppBadge`

| `variant` | Use |
|-----------|-----|
| `featured` | Gold-tinted — Featured opportunities |
| `success` | Applied, published |
| `warning` | Closing soon |
| `danger` | Rejected, expired |
| `secondary` | Category chips |
| `muted` | Neutral tags |

### `AppAlert`

`variant`: `info` | `success` | `warning` | `danger`. Optional `title`.

### `AppModal`

`v-model` open state, `title`, `size` (`sm` | `md` | `lg`), default slot + `#footer`. Escape and backdrop close.

### Layout

`layouts/AppLayout.vue` — site chrome, auth-aware nav. Wrap all pages.

---

## Opportunity card pattern (Phase 2)

```text
┌─────────────────────────────────────────────┐
│ Title                        [Featured]     │
│ Organization (secondary color)              │
│ Location · Deadline (warning if soon)       │
│ Excerpt (muted)                             │
│ [ View opportunity ]  [ Save outline ]      │
└─────────────────────────────────────────────┘
```

---

## Dark mode

System preference only (`prefers-color-scheme`). No in-app toggle yet. Tokens swap via `:root` in `app.css`.

---

## Do not use

- Vue starter `--vt-c-indigo`, starter teal link color, or ad hoc `#b42318` outside `danger` token.
- Gold for every Apply button.
- Green for non-success UI (e.g. primary CTAs).

---

## Related docs

- [`ROADMAP.md`](../ROADMAP.md) — build phases  
- [`PHASE0-DESIGN.md`](./PHASE0-DESIGN.md) — domain ERD  
- [`API.md`](./API.md) — API contract  
