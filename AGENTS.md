# DOX framework

- DOX is highly performant AGENTS.md hierarchy installed here
- Agent must follow DOX instructions across any edits

## Core Contract

- AGENTS.md files are binding work contracts for their subtrees
- Work products, source materials, instructions, records, assets, and durable docs must stay understandable from the nearest applicable AGENTS.md plus every parent AGENTS.md above it

## Read Before Editing

1. Read the root AGENTS.md
2. Identify every file or folder you expect to touch
3. Walk from the repository root to each target path
4. Read every AGENTS.md found along each route
5. If a parent AGENTS.md lists a child AGENTS.md whose scope contains the path, read that child and continue from there
6. Use the nearest AGENTS.md as the local contract and parent docs for repo-wide rules
7. If docs conflict, the closer doc controls local work details, but no child doc may weaken DOX

Do not rely on memory. Re-read the applicable DOX chain in the current session before editing.

## Update After Editing

Every meaningful change requires a DOX pass before the task is done.

Update the closest owning AGENTS.md when a change affects:

- purpose, scope, ownership, or responsibilities
- durable structure, contracts, workflows, or operating rules
- required inputs, outputs, permissions, constraints, side effects, or artifacts
- user preferences about behavior, communication, process, organization, or quality
- AGENTS.md creation, deletion, move, rename, or index contents

Update parent docs when parent-level structure, ownership, workflow, or child index changes. Update child docs when parent changes alter local rules. Remove stale or contradictory text immediately. Small edits that do not change behavior or contracts may leave docs unchanged, but the DOX pass still must happen.

## Hierarchy

- Root AGENTS.md is the DOX rail: project-wide instructions, global preferences, durable workflow rules, and the top-level Child DOX Index
- Child AGENTS.md files own domain-specific instructions and their own Child DOX Index
- Each parent explains what its direct children cover and what stays owned by the parent
- The closer a doc is to the work, the more specific and practical it must be

## Child Doc Shape

- Create a child AGENTS.md when a folder becomes a durable boundary with its own purpose, rules, responsibilities, workflow, materials, or quality standards
- Work Guidance must reflect the current standards of the project or user instructions; if there are no specific standards or instructions yet, leave it empty
- Verification must reflect an existing check; if no verification framework exists yet, leave it empty and update it when one exists

Default section order:
- Purpose
- Ownership
- Local Contracts
- Work Guidance
- Verification
- Child DOX Index

## Style

- Keep docs concise, current, and operational
- Document stable contracts, not diary entries
- Put broad rules in parent docs and concrete details in child docs
- Prefer direct bullets with explicit names
- Do not duplicate rules across many files unless each scope needs a local version
- Delete stale notes instead of explaining history
- Trim obvious statements, repeated rules, misplaced detail, and warnings for risks that no longer exist

## Closeout

1. Re-check changed paths against the DOX chain
2. Update nearest owning docs and any affected parents or children
3. Refresh every affected Child DOX Index
4. Remove stale or contradictory text
5. Run existing verification when relevant
6. Report any docs intentionally left unchanged and why

## User Preferences

When the user requests a durable behavior change, record it here or in the relevant child AGENTS.md

## Project Overview

ThunderfamEats Backend is the server that powers the ThunderfamEats multi-restaurant
food-ordering / delivery / booking platform. It is a **Yii 1.x (PHP) application** based
on the *Karenderia Multiple Restaurant System (KMRS)*, customized for ThunderfamEats.

It serves three surfaces from one codebase:

1. **Public storefront + customer web** — themed HTML site (`themes/karenderia_v2`) rendered
   by the front application (`protected/`), entry `index.php`.
2. **JSON APIs** — consumed by the ThunderfamEats mobile apps (customer, driver, merchant,
   kitchen, POS) and by payment webhooks. Served mainly by the `Interface*`, `Api*`,
   `Partnerapi`, `Pv1`, `Payv1`, `Chatapi` and `Driver*` controllers.
3. **Back office / admin** — a second bundled Yii app under `backoffice/` for platform
   administration and merchant management.

- **Framework:** Yii 1.1 (bundled in `framework/`, ~29 MB, no Composer at root).
- **Runtime config:** `k-config.php` (git-ignored) defines DB credentials, `CRON_KEY`,
  backoffice folder, demo flags. Boot order: `index.php` → `k-config.php` →
  `protected/config/front_main.php` → Yii.
- **Database:** MySQL/MariaDB, table prefix `st_`, accessed via Yii ActiveRecord
  (`protected/models/AR_*`) and raw SQL in components.
- **Cache:** Redis when available (`RedisHelper`), else file cache.
- **Async work:** DB-backed job queue (`AR_job_queue` + `protected/components/jobs/*`)
  drained by the `processjobs` console command; plus scheduled cron commands
  (`protected/commands/*`).
- **Payments:** ~60 gateway modules under `protected/modules/*` (Stripe, PayPal, Razorpay,
  Vivawallet/Touchpay, MTN, Flutterwave, Paystack, etc.).
- **Notifications/realtime:** Firebase Cloud Messaging + Firestore chat, Pusher, SMS/email
  providers (`CFirebase`, `CSMSsender`, `CEmailer`, SendGrid/Mailgun/Mailjet extensions).

## Repository Map

- `index.php` — front controller / bootstrap. See [docs](docs/index.php.md).
- `k-config.php` — **git-ignored** secrets. Template: `k-config.sample.php`
  (see [docs](docs/k-config.sample.php.md)).
- `protected/` — the front/API application (controllers, models, components, jobs,
  commands, modules, config). Owns most business logic. See `protected/AGENTS.md`.
- `backoffice/` — the admin/back-office Yii application. See `backoffice/AGENTS.md`.
- `framework/` — bundled Yii 1.1 framework. **Do not edit**; treat as vendored runtime.
- `themes/karenderia_v2/` — front-end theme (views + assets) rendered by `protected/`.
- `assets/`, `backoffice/assets/` — published web assets. Hashed subfolders are generated
  and git-ignored.
- `upload/` — **git-ignored** user media + (historically) leaked service-account keys.
- `karenderia.sql` — **git-ignored** database dump / seed.
- `backup1/`, `touchpay-backup-*/` — **git-ignored** local backup copies.
- `.well-known/`, `robots.txt`, `web.config`, `.htaccess`, `service-worker.js`,
  `sitemap` — web-server / SEO / PWA glue.

## Documentation Convention

Two complementary layers, following the same architecture as the ThunderfamEats Customer App:

1. **DOX (`AGENTS.md`)** — binding contracts per subtree (this framework). Update on every
   meaningful change per the rules above.
2. **Reference docs (`docs/`)** — a master index (`docs/README.md`) plus per-file
   `<filename>.md` docs describing purpose, responsibilities, entry points, dependencies,
   DB interactions, endpoints, inputs/outputs, side effects, security and error handling.

The master reference index is [`docs/README.md`](docs/README.md). The engineering journal
and change log is [`CLAUDE.md`](CLAUDE.md) — append a dated entry there on every change.

## Security Contract (repo-wide, must not be weakened)

- **Never commit secrets.** `k-config.php`, `.env*` (except `*.sample`), `*.pem`/`*.key`/
  `*.p12`/`*.keystore`, `*firebase-adminsdk*.json`, `google-services.json`, `karenderia.sql`,
  `upload/`, backups, `protected/runtime/*` and `phpinfo.php` are git-ignored. Verify with
  `git check-ignore <path>` before adding new config.
- Configuration that the app needs is committed only as a sanitized `*.sample` template.
- Do not log tokens, card data, or PII. API auth uses per-user tokens; webhook routes are
  CSRF-exempt (see `front_main.php` `noCsrfValidationRoutes`) and must validate signatures.

## Child DOX Index

- `protected/AGENTS.md` — front/API application: controllers, models, components, jobs,
  commands, modules, config. Owns the bulk of business logic.
- `backoffice/AGENTS.md` — admin / back-office application.

`framework/` (vendored Yii) and `themes/` (view layer) are owned by this root doc until
they grow their own durable contracts.