# Hiqbah — Coffee Roastery Systems

Source for the systems run by Hiqbah coffee roastery (مقهى و محمصة حقبة). This is a
**sanitized share repository**: source code, database migrations, tests and documentation
only. It carries no credentials, no environment files, no database exports and no
customer or employee data.

> **Security.** Never commit a real `.env`. Every project here ships an `.env.example`
> listing the variable names with placeholder values — copy it to `.env` locally and fill
> it in. `.gitignore` at the repository root blocks `.env`, private keys, database dumps
> and captured auth state.

---

## Repository structure

| Path | What it is | Stack |
|---|---|---|
| `erp/` | **BeanFlow ERP** — the production system. Orders, roasting, QC, packaging, dispatch, inventory and accounting for the roastery. | Next.js 16, TypeScript, Prisma, PostgreSQL |
| `erp-mobile/` | Mobile companion application | React Native / Expo |
| `hqba/` | HQBA — an earlier bean-to-cup traceability system, kept for reference | Laravel 12 API + React 19 SPA |

`erp/` is the canonical, current ERP source. It is exported from the released state of the
production branch, not from a development working copy.

---

## BeanFlow ERP (`erp/`)

The system a roastery actually runs on: a customer order is raised, stock is checked,
green coffee is roasted against what is still owed, quality control passes or rejects the
batch, the roast is packaged, and the finished goods are dispatched — with every kilogram
traceable from the green bean lot to the delivery note.

### Notable domain behaviour

- **One packaging operation.** Packaging does not ask which *kind* of packaging is being
  done. An operator describes the packages they filled and the weight that went into each;
  the system decides what that means for inventory.
- **Every gram reconciles.** What leaves a roast lands in exactly one of four places:
  complete packages, partial packages, declared loss, or the unpacked remainder.
- **Partial packages are real inventory.** A bag that did not reach its labelled weight is
  recorded, visible and toppable-up — but it can never be sold, reserved or dispatched as a
  full unit until it is completed.
- **Loss is declared, not inferred.** Deriving loss from a shortfall cannot distinguish
  spilled coffee from a mistyped weight, so an operator states it with a reason.

### Prerequisites

- Node.js 20 or newer
- A PostgreSQL 16 database (the application has no local fallback and will refuse to start
  without one)

### Setup

```bash
cd erp
npm ci
cp .env.example .env        # then fill in the values
npx prisma generate
npx prisma migrate deploy   # apply migrations to your own database
npm run dev
```

### Environment variables

Names only — see `erp/.env.example` for the full annotated template.

| Variable | Required | Purpose |
|---|---|---|
| `DATABASE_URL` | yes | PostgreSQL connection (pooled) |
| `DIRECT_URL` | yes | PostgreSQL connection (direct, used for migrations) |
| `JWT_SECRET` | yes | Session signing. Minimum 32 characters. |
| `PIN_LOOKUP_SECRET` | yes | Keyed lookup for PIN sign-in. Its own secret, never reused. |
| `RATE_LIMIT_SECRET` | no | Pepper for rate-limit keys; falls back to `JWT_SECRET`. |
| `TRANSLATION_API_KEY` | no | Google Cloud Translation; falls back to a free service. |

`PIN_LOOKUP_SECRET` cannot be rotated as a configuration change on its own — a stored
lookup does not reveal which key produced it, so rotating it without reissuing every PIN
locks every employee out. See the notes in `.env.example`.

### Database migrations

```bash
npx prisma migrate deploy   # apply pending migrations
npx prisma migrate status   # show what is applied and what is pending
```

Migrations are **expand-only by convention**: a schema change is additive so the currently
deployed application keeps working against the new schema, and any destructive follow-up
happens only after the compatible build is live.

### Tests

```bash
npm run build                                  # production build
npx tsc --noEmit                               # typecheck
node scripts/e2e/regression/run-all.mjs        # backend regression suite
npx playwright test                            # browser UAT (needs a running server)
```

The regression suite drives a **disposable test database** through the real HTTP API and
asserts on state read back from the database. It refuses to run against a database it does
not recognise as disposable. The browser tests sign in through the real keypad and drive
the real screens, including at tablet width and in the Arabic right-to-left layout.

---

## HQBA (`hqba/`)

An earlier bean-to-cup traceability system built around the **Crop (S/N)** as the central
entity. Laravel 12 API with a React 19 SPA front end; see `hqba/ARCHITECTURE.md` for the
module map.

```bash
cd hqba/backend && composer install && cp .env.example .env && php artisan migrate
cd hqba/frontend && npm install && npm run dev
```

> ### ⚠️ HQBA ships with insecure bootstrap credentials — change them
>
> `hqba/backend/database/migrations/2026_04_28_140000_seed_admin_user.php` creates a
> `super_admin` account during migration with a **well-known default password and a
> `000000` PIN**. It runs automatically as part of `php artisan migrate`.
>
> This is fine for a throwaway local database and unacceptable anywhere reachable. After
> the first migration on any shared or deployed environment, change that account's
> password and PIN immediately — or delete the account and create your own.
>
> The defaults are left in the source because they are how the project bootstraps, not
> because they are safe.

---

## Running migrations safely

The database commands in this README create and modify tables. Point them at a database
you are willing to lose.

- Use a **local or disposable development database**, configured explicitly in your own
  `.env`. Never aim a migration, a seed or a test run at a production database.
- The ERP refuses to seed unless `ERP_SEED_ENABLED=true` is set, and its seed and test
  harness additionally refuse specific database hosts **by name**. Those host names appear
  in `erp/prisma/seed.ts` and in the regression self-test: they are hostnames, never
  credentials, and they are listed there so the guard can refuse them. Removing them to
  tidy the source would switch the protection off.

---

## Ownership and authorship

This repository is published by its owner. `hqba/` is an included project owned by the
repository owner; it was originally developed by others, and original commit authorship
and any third-party copyright and licence notices are preserved as they were found.
Third-party dependency metadata under `hqba/backend/composer.lock` and the various
`package-lock.json` files carries upstream package authors' details, which are already
public in their respective registries.

---

## What is deliberately not here

Environment files, database credentials, API keys, database dumps, production data
exports, build output, `node_modules`, `vendor/`, test reports, and internal operational
runbooks. Some files name non-production database hosts inside **safety guards** that
refuse to run destructive operations against them — those are protection mechanisms and
are kept intact on purpose.
