# protected — Front / API Application

## Purpose

Owns the customer-facing storefront, all JSON APIs (mobile apps + payment webhooks), and the
business logic behind them. This is the primary Yii 1.x application; `index.php` at the repo
root boots it via `config/front_main.php`.

## Ownership

- This contract governs everything under `protected/`.
- Each major subfolder has (or should grow) its own `AGENTS.md` for local rules.
- Cross-cutting concerns defined here — the API envelope, auth model, job/cron model,
  config/boot order — must not be weakened by child docs.
- `backoffice/` is a separate application (its own `AGENTS.md`); several front configs alias
  into it (`Yii::setPathOfAlias('backend', ...backoffice/protected)`), so backend models and
  components are importable from the front app.

## Architecture (layers)

1. **Config / boot** (`config/`) — `front_main.php` (web app config: modules, components,
   URL rules, CSRF-exempt routes, DB), `console.php` (CLI app), `params.php` (constants +
   helper functions `t()`, `q()`, `dump()`), `mail.php`. Depends on constants from the
   root `k-config.php`.
2. **Controllers** (`controllers/`) — request handlers. Web controllers render theme views;
   `Interface*`/`Api*`/`Partnerapi`/`Pv1`/`Payv1`/`Chatapi`/`Driver*` return JSON. `*Common.php`
   files are shared base controllers. See `controllers/AGENTS.md`.
3. **Models** (`models/`) — `AR_*` ActiveRecord classes mapping `st_`-prefixed tables. See
   `models/AGENTS.md`.
4. **Components** (`components/`) — services and helpers (`C*` classes): payments, orders,
   firebase, SMS, maps, identities/auth, widgets. See `components/AGENTS.md`.
5. **Jobs** (`components/jobs/`) — units of async work drained from `AR_job_queue`. See
   `commands/AGENTS.md` for how they are run.
6. **Commands** (`commands/`) — Yii console commands: the `processjobs` queue worker and
   scheduled cron tasks. See `commands/AGENTS.md`.
7. **Modules** (`modules/`) — payment gateways and packaged feature add-ons (Mobile apps,
   driver, kitchen, POS). See `modules/AGENTS.md`.
8. **Extensions / vendor** (`extensions/`, `vendor/`) — third-party libraries (SendGrid,
   Mailgun, Google API client, Firebase PHP SDK, JWT, Razorpay SDK). Vendored, no Composer
   at root; treat as read-only.

## Local Contracts

- **API envelope:** JSON responses use `{ code, msg, details }`. `code === 1` = success
  (some endpoints also accept `3`); errors carry a human message in `msg`. Keep new endpoints
  consistent with this shape.
- **Auth:** APIs authenticate with per-user tokens (customer/driver/merchant/kitchen
  identities in `components/App*Identity.php`, `CustomerIdentity*`, `MerchantIdentity*`,
  `DriverIdentity`). Web auth uses Yii `user`/`merchant`/`driver` user components
  (`front_main.php`) with auto-login cookies.
- **CSRF:** web forms are CSRF-validated; API and webhook route prefixes are exempt via
  `noCsrfValidationRoutes` in `config/front_main.php`. Any new API/webhook route must be
  added there, and webhooks must verify provider signatures themselves.
- **DB access:** prefer `AR_*` ActiveRecord; raw SQL must use `q()` / parameter binding
  (never string-concatenate user input). Table prefix is `st_` (`DB_PREFIX`).
- **Async:** long or side-effectful work (emails, push, printing, refunds, status fan-out)
  is enqueued as a job in `AR_job_queue` and executed by a `jobs/*` class, not done inline.

## Work Guidance

- New business logic belongs in a component (`C*`) or job, not in a controller action body.
- Reuse existing components/models before adding new ones; this codebase has heavy overlap.
- Do not edit `framework/` or vendored `vendor/`/`extensions/` third-party code.
- Never log secrets, tokens, card data or PII.
- User-facing strings should go through the translation helper `t()` / `Yii::t()`.

## Verification

No automated test suite is present. Verify changes by exercising the affected endpoint or
page against a local DB (seed from `karenderia.sql`) and checking `protected/runtime/
application.log` for errors. Add tests here when a framework is introduced.

## Child DOX Index

- `controllers/AGENTS.md` — request handlers (web + JSON API + webhooks).
- `models/AGENTS.md` — `AR_*` ActiveRecord data layer.
- `components/AGENTS.md` — service/helper classes and async jobs.
- `commands/AGENTS.md` — console queue worker and cron commands.
- `modules/AGENTS.md` — payment gateways and packaged feature modules.

`config/`, `extensions/`, `vendor/`, `messages/`, `filters/`, `views/`, `runtime/` are owned
by this doc. Related reference docs live in [`/docs`](../docs/README.md).
