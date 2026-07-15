# backoffice — Admin / Back-office Application

## Purpose

A second, separate Yii 1.x application for platform administration and merchant management:
the admin dashboard, merchant/back-end APIs, reports, catalog/attribute management, order
archiving, and admin-side tools. Entry: `backoffice/index.php` → `protected/config/
backend_main.php`. Much larger controller/model surface than the front app (≈85 controllers,
≈165 models).

## Ownership

Governs everything under `backoffice/`. Shares the same repo-root `k-config.php` (DB creds,
`CRON_KEY`) and the shared `framework/` with the front app. Inherits the repo-wide Security
Contract from the root `AGENTS.md`.

## Relationship to the front app

- The front app (`protected/`) aliases into here: `backend` →
  `backoffice/protected`, so `backend.models.*` / `backend.components.*` are importable from
  the front. Front and back office therefore operate on the **same database**.
- `BACKOFFICE_FOLDER` in `k-config.php` names this directory; changing the folder name
  requires updating that constant.

## Structure

- `protected/config/` — `backend_main.php` (admin web app config; DB, `key`/`secret`
  placeholders), `params.php`, `htmlpurifier.php`.
- `protected/controllers/` — admin + `Api*`/`Backend*` controllers (dashboard, merchants,
  buyers, commission, reports, attributes, bookings, tools, tasks).
- `protected/models/` — admin data layer (superset of front models).
- `protected/migrations/` — Yii DB migrations for the admin schema.
- `protected/modules/`, `extensions/`, `vendor/` — admin modules and vendored libs
  (includes `firebase-php`, `ar-php`, google client).
- `assets/`, `themes/`, `twig/` — admin UI. Hashed `assets/<hash>/` folders are generated
  and git-ignored.

## Local Contracts

- Admin endpoints are privileged; keep authentication/authorization checks intact —
  a regression here exposes the whole platform.
- Schema changes go through `protected/migrations/` (Yii migrations) here, unlike the front
  app's module `filesupdate/*.sql`. Record them in [`CLAUDE.md`](../CLAUDE.md).
- Do not edit vendored `vendor/` / `extensions/` third-party code.
- No real credentials in `backend_main.php`; gateway keys live per-merchant in the DB.

## Do-not-touch-blindly

- `backend_main.php` — wires the entire admin app (DB, modules, routes).
- `protected/migrations/` — applied migrations are historical; add new ones, don't rewrite.

## Verification

No automated tests. Verify by exercising the affected admin screen/API against a local DB and
watching `backoffice/protected/runtime/` logs.

## Related documentation

- Front app: [`../protected/AGENTS.md`](../protected/AGENTS.md)
- Repo-wide rules: [`../AGENTS.md`](../AGENTS.md)
- Reference docs: [`/docs`](../docs/README.md)
