# k-config.sample.php

Reference doc for [`/k-config.sample.php`](../k-config.sample.php). Owning contract:
[`/AGENTS.md`](../AGENTS.md).

## File purpose

Sanitized template for the real runtime configuration file `k-config.php`. The live
`k-config.php` holds **secrets** (database password, cron key) and is git-ignored; this
`.sample` is the committed, placeholder-only version each environment copies and fills in.

## Responsibilities

Define the constants the application requires at the very start of boot (before Yii loads):

| Constant | Meaning |
|----------|---------|
| `DB_NAME` / `DB_USER` / `DB_PASSWORD` | MySQL/MariaDB credentials |
| `DB_HOST` | DB host (and optional `:port`) |
| `DB_CHARSET` | connection charset (`utf8`) |
| `DB_PREFIX` | table prefix (`st_`) |
| `KMRS_DEFAULT_LANGUAGE` | default UI language |
| `BACKOFFICE_FOLDER` | directory name of the admin app (`backoffice`) |
| `CRON_KEY` | shared secret guarding cron/task endpoints |
| `DEMO_MODE` / `DEMO_MERCHANT` | demo restrictions |

## Setup / entry points

Copy to `k-config.php` and edit:

```
cp k-config.sample.php k-config.php   # then set real values
```

Loaded by both `index.php` and `backoffice/index.php` via `require_once`.

## Dependencies

None. Pure `define()` calls; must run before Yii and before any config that references
`DB_*`, `BACKOFFICE_FOLDER` or `CRON_KEY`.

## Security considerations

- **Never commit the real `k-config.php`.** It is git-ignored (`/k-config.php`) and was found
  during the audit to contain a live DB password and `CRON_KEY`.
- Use a long random `CRON_KEY` (e.g. `bin2hex(random_bytes(16))`).
- Keep `DEMO_MODE = false` in production.
- Restrict the DB user to the minimum required privileges.

## Known limitations

- Secrets live in a PHP `define()` file rather than a secrets manager or `.env` loader.

## Future improvements

- Migrate to environment variables / a secrets manager; keep the sample in sync.

## Related files

- [`index.php.md`](index.php.md), [`front_main.php.md`](front_main.php.md),
  [`console.php.md`](console.php.md) — all consume these constants.
- `.gitignore` — enforces that `k-config.php` and other secrets stay untracked.
