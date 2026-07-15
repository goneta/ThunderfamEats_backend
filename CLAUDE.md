# CLAUDE.md — ThunderfamEats Backend Engineering Journal

Persistent engineering journal and AI working memory for the ThunderfamEats Backend.
Read this first, then the DOX chain ([`AGENTS.md`](AGENTS.md) and children) and the reference
docs in [`docs/`](docs/README.md). **Append** a dated entry to the Change Log on every change;
never overwrite prior entries.

---

## Project overview

ThunderfamEats Backend is a **Yii 1.x (PHP)** application based on the *Karenderia Multiple
Restaurant System (KMRS)*, customized for ThunderfamEats. It powers a multi-restaurant food
ordering / delivery / booking platform and serves three surfaces from one repo:

1. **Storefront + customer web** — themed site (`themes/karenderia_v2`) rendered by the front
   app (`protected/`), entry `index.php`.
2. **JSON APIs** — for the customer, driver, merchant, kitchen and POS mobile apps, plus
   payment webhooks.
3. **Back office** — a second bundled Yii app (`backoffice/`) for administration.

## Repository structure

```
/                     front app bootstrap (index.php), web-server glue, k-config.*
  protected/          front/API application (controllers, models, components, jobs,
                      commands, modules, config, extensions, vendor)
  backoffice/         admin application (own controllers/models/migrations/config)
  framework/          bundled Yii 1.1 (vendored; do not edit)
  themes/             karenderia_v2 views + assets (front view layer)
  assets/             published web assets
  docs/               per-file reference docs + master index
  upload/  *ignored*  user media (also historically held leaked service-account keys)
  backup1/ *ignored*  local full-copy backup
```
Full map: [`AGENTS.md`](AGENTS.md) → Repository Map. Directory contracts: the `AGENTS.md` in
each area. Per-file docs: [`docs/README.md`](docs/README.md).

## Coding standards

- Yii 1.x conventions: controllers thin, logic in components (`C*`) and models (`AR_*`).
- Table prefix `st_` via `DB_PREFIX`; never hardcode the prefix, use AR / `{{table}}`.
- SQL: AR conditions or the `q()` helper / parameter binding — never concatenate request input.
- Side effects (email/push/print/refund/status fan-out) run as `AR_job_queue` jobs, not inline.
- User-facing strings via `t()` / `Yii::t()`.
- Do not edit `framework/` or vendored `vendor/` / `extensions/` code.
- Never log or commit secrets, tokens, card data or PII.

## Architecture decisions

- **Single shared DB** across front and back office; the front app imports `backend.*`
  aliases into `backoffice/protected`.
- **Cache:** Redis when available (`RedisHelper`), file cache otherwise — chosen at config
  time in `front_main.php`.
- **Async model:** DB-backed queue (`AR_job_queue`) drained by the `processjobs` console
  command; scheduled work via `commands/Process*` (or web `Task*Controller`, guarded by
  `CRON_KEY`, for hosts without shell cron).
- **Payments:** one module per gateway under `protected/modules/*`; credentials stored
  per-merchant in the DB, not in code. Webhooks are CSRF-exempt and must verify signatures.
- **Vendored framework:** Yii and third-party libs are committed (no Composer at root) so the
  repo is directly deployable.

## Development guidelines

- Before editing, read the DOX chain from root to the target path (see `AGENTS.md`).
- New API/webhook routes must be added to `noCsrfValidationRoutes` in `front_main.php`.
- Verify by exercising the affected page/endpoint against a local DB (seed from
  `karenderia.sql`) and checking `protected/runtime/application.log`. No automated tests exist.
- Update the relevant `docs/*.md`, the owning `AGENTS.md`, and append a Change Log entry here.

## Security considerations

- `k-config.php` (DB password + `CRON_KEY`) is **git-ignored**; use `k-config.sample.php` as
  the template. See [`docs/k-config.sample.php.md`](docs/k-config.sample.php.md).
- `.gitignore` blocks `.env*` (except `*.sample`), `*.pem`/`*.key`/`*.p12`/`*.keystore`,
  `*firebase-adminsdk*.json`, `google-services.json`, `karenderia.sql`, `upload/`, backups,
  `protected/runtime/*` and `phpinfo.php`. Verify new config with `git check-ignore`.
- `YII_DEBUG` is `true` in both `index.php` files — must be `false` in production.
- Firebase **service-account private keys** exist under the (ignored) `upload/` and `backup1/`
  trees. They are excluded from git but remain on disk and were exposed via a public upload
  path — **rotate them** (see Known issues).

## Known issues / Technical debt

- **Exposed secrets (rotate):** the working tree's live `k-config.php` DB password and
  `CRON_KEY`, plus Firebase admin-SDK JSON keys under `upload/`, were present in plaintext.
  Excluded from git, but should be rotated since they lived in a servable path.
- `YII_DEBUG = true` in production entry points (info disclosure).
- `phpinfo.php` present at repo root (info disclosure) — excluded from git; delete from the
  server.
- No automated tests.
- No Composer/dependency manager at root; framework and libs are vendored, so the blanket
  `*.pem`/`*.key` ignore also excludes vendored CA bundles (e.g. razorpay `cacert.pem`) that
  some HTTP clients expect — re-add per-library if SSL verification fails.
- Monolithic controllers (`InterfaceController` ~11k lines, `ApiController` ~7k lines).
- Module list duplicated between `front_main.php` and `console.php`.

## Completed work

- DOX documentation tree initialized (root + child `AGENTS.md`, `docs/` reference layer).
- Security audit + `.gitignore` + sanitized `k-config.sample.php`; initial repository import.

## Current work in progress

- None open. See Change Log for the latest session.

## Pending tasks / Future roadmap

- Rotate all exposed credentials (DB, `CRON_KEY`, Firebase service accounts).
- Set `YII_DEBUG = false` and remove `phpinfo.php` on the server.
- Expand per-file `docs/*.md` coverage to remaining key controllers/components.
- Introduce automated tests and, ideally, Composer-managed dependencies.
- Consider Git LFS or a separate store for large media if any binaries must be versioned.

## Database changes

- None made in code. Schema originates from `karenderia.sql` (ignored dump); module schema
  installers live in `protected/modules/*/filesupdate/*.sql`; admin migrations in
  `backoffice/protected/migrations/`.

## API changes

- None. API surface documented in
  [`docs/InterfaceController.php.md`](docs/InterfaceController.php.md) and
  [`protected/controllers/AGENTS.md`](protected/controllers/AGENTS.md).

## Deployment notes

- Copy `k-config.sample.php` → `k-config.php` and fill real values on each environment.
- Ensure `framework/` and vendored libs are present (they are committed).
- Run the `processjobs` worker continuously / on a short cron interval, plus the scheduled
  `Process*` commands. Set `YII_DEBUG = false`.

---

## Change Log

### 2026-07-15 — Repository security hardening + DOX documentation initialization

**What was changed**
- Performed a full security audit of the untracked codebase before the first real commit
  (previously only `README.md` was tracked).
- Added a comprehensive [`.gitignore`](.gitignore) covering secrets, DB dumps, user uploads,
  backups, runtime/cache, OS/IDE files, `node_modules`, and diagnostic endpoints.
- Added sanitized [`k-config.sample.php`](k-config.sample.php) template (placeholders only);
  kept the real `k-config.php` git-ignored.
- Initialized the DOX documentation tree: expanded the root [`AGENTS.md`](AGENTS.md) with
  project overview / repo map / security contract / child index; created child `AGENTS.md`
  for `protected/`, `protected/controllers/`, `protected/models/`, `protected/components/`,
  `protected/commands/`, `protected/modules/`, and `backoffice/`.
- Created the reference-doc layer under [`docs/`](docs/README.md): master index plus per-file
  docs for `index.php`, `k-config.sample.php`, `front_main.php`, `console.php`, `params.php`,
  and `InterfaceController.php`.
- Created this `CLAUDE.md`.

**Why**
- The repo was about to receive its first full import; live DB credentials, a `CRON_KEY`,
  Firebase service-account private keys, a 400 MB `upload/` tree, DB dumps and local backups
  were all untracked and at risk of being committed and pushed publicly.
- The user requested the same DOX documentation architecture used for the ThunderfamEats
  Customer App, to serve as long-term technical documentation.

**Files modified / added**
- Added: `.gitignore`, `k-config.sample.php`, `CLAUDE.md`, `docs/README.md`,
  `docs/index.php.md`, `docs/k-config.sample.php.md`, `docs/front_main.php.md`,
  `docs/console.php.md`, `docs/params.php.md`, `docs/InterfaceController.php.md`,
  `protected/AGENTS.md`, `protected/controllers/AGENTS.md`, `protected/models/AGENTS.md`,
  `protected/components/AGENTS.md`, `protected/commands/AGENTS.md`,
  `protected/modules/AGENTS.md`, `backoffice/AGENTS.md`.
- Modified: `AGENTS.md` (replaced the "not yet indexed" placeholder with the real index).

**Architectural decisions**
- Keep the vendored `framework/` (29 MB) and library trees committed (no Composer at root; the
  repo must stay directly deployable).
- Use a security-first blanket ignore for `*.pem`/`*.key` etc., accepting that a few vendored
  public CA bundles are also excluded (documented as re-addable if needed).
- Keep module `filesupdate/*.sql` schema installers versioned (DDL, not data dumps).

**Security improvements**
- No secrets, keys, dumps, uploads or backups are staged for commit (verified via
  `git check-ignore` and a pattern scan of the staged file list).

**Remaining work / Suggested next steps**
- **Rotate** the DB password, `CRON_KEY`, and all Firebase service-account keys that were
  present in plaintext on disk / a servable path.
- Set `YII_DEBUG = false` and remove `phpinfo.php` from the server.
- Continue expanding `docs/*.md` for remaining key controllers/components and back-office.
- Add automated tests; evaluate migrating dependencies to Composer.
