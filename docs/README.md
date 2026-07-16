# ThunderfamEats Backend — Documentation Index

Master reference index for the backend. This is the browsable, human + AI oriented layer that
complements the binding `AGENTS.md` (DOX) contracts. Start here, then drill into a subtree's
`AGENTS.md` for rules and the per-file `*.md` docs below for detail.

## How the documentation is organized

- **DOX / `AGENTS.md`** — binding per-directory contracts. Root: [`/AGENTS.md`](../AGENTS.md).
- **`docs/*.md`** — per-file reference docs (this folder), named `<filename>.md`.
- **`CLAUDE.md`** — [engineering journal & change log](../CLAUDE.md). Append an entry per change.

## Architecture at a glance

`index.php` boots the **front/API app** (`protected/`) via `k-config.php` +
`protected/config/front_main.php`. `backoffice/index.php` boots the **admin app**. Both share
one MySQL database (`st_` prefix), the bundled Yii 1.1 `framework/`, and `k-config.php`.
Requests hit controllers → components (`C*` services) → `AR_*` models; side effects are
enqueued as jobs and drained by the `processjobs` console command.

```
HTTP ─▶ index.php ─▶ front_main.php ─▶ Controller ─▶ Component (C*) ─▶ AR_* model ─▶ MySQL
                                             └─▶ AR_job_queue ─▶ processjobs ─▶ jobs/* (email, push, refund…)
```

## Directory contracts (AGENTS.md)

| Area | Contract | What it owns |
|------|----------|--------------|
| Repo root | [`/AGENTS.md`](../AGENTS.md) | Project overview, repo map, security contract |
| Front/API app | [`protected/AGENTS.md`](../protected/AGENTS.md) | Controllers, models, components, jobs, commands, modules, config |
| Controllers | [`protected/controllers/AGENTS.md`](../protected/controllers/AGENTS.md) | Web pages, JSON APIs, webhooks |
| Models | [`protected/models/AGENTS.md`](../protected/models/AGENTS.md) | `AR_*` ActiveRecord data layer |
| Components | [`protected/components/AGENTS.md`](../protected/components/AGENTS.md) | `C*` services, identities, jobs |
| Commands | [`protected/commands/AGENTS.md`](../protected/commands/AGENTS.md) | Queue worker + cron |
| Modules | [`protected/modules/AGENTS.md`](../protected/modules/AGENTS.md) | Payment gateways + feature add-ons |
| Back office | [`backoffice/AGENTS.md`](../backoffice/AGENTS.md) | Admin application |
| Front-end theme | [`themes/karenderia_v2/AGENTS.md`](../themes/karenderia_v2/AGENTS.md) | Storefront/customer views, CSS, redesign layer |

## Per-file reference docs

- [`index.php.md`](index.php.md) — front controller / bootstrap
- [`k-config.sample.php.md`](k-config.sample.php.md) — configuration template (secrets)
- [`front_main.php.md`](front_main.php.md) — front web app configuration (modules, routes, CSRF, DB)
- [`console.php.md`](console.php.md) — console (CLI) app configuration
- [`params.php.md`](params.php.md) — app constants + global helper functions
- [`InterfaceController.php.md`](InterfaceController.php.md) — primary customer/mobile JSON API
- [`custom.css.md`](custom.css.md) — front-end UI redesign layer (light/dark theming)

New per-file docs should follow the same template (purpose, responsibilities, entry points,
public API, dependencies, DB interactions, endpoints, inputs/outputs, side effects, security,
error handling, performance, limitations, future work, related files). Add each new doc to the
list above.

## Conventions

- File docs are named exactly `<source-filename>.md`.
- Cross-link related docs and the owning `AGENTS.md`.
- Keep docs in sync with code; a code change that alters behavior must update its `*.md`,
  the owning `AGENTS.md`, and add a dated [`CLAUDE.md`](../CLAUDE.md) entry.
