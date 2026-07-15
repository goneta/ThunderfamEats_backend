# protected/config/console.php

Reference doc for [`/protected/config/console.php`](../protected/config/console.php).
Owning contract: [`protected/commands/AGENTS.md`](../protected/commands/AGENTS.md).

## File purpose

Configuration array for the **console (CLI) application** — the context in which the queue
worker and scheduled cron commands run.

## Responsibilities

- Set `IS_FRONTEND`, path aliases (`backend`, `upload_dir`, `modules_dir`, `home_dir`).
- Import the same model/component/job/controller/vendor namespaces as the web app.
- Register modules (mirrors the web app's gateway list).
- Map custom console commands via `commandMap` (notably `processjobs` →
  `application.commands.ProcessJobsCommand`).
- Configure `db` (same `DB_*` constants / `st_` prefix as the web app), `messages`,
  file logging (`application.log`), and `CmsInput`.
- Merge in `params.php`.

## Entry points

Loaded by the Yii console runner (`yiic`/console bootstrap) when running commands, e.g.
`processjobs` and the `Process*`/`Assign*` cron commands.

## Dependencies

- Constants from `k-config.php` (`DB_*`, `BACKOFFICE_FOLDER`).
- `protected/components/RedisHelper.php`, `params.php`, the `commands/*` and `jobs/*` classes.

## Database interactions

Defines the same shared `db` connection the web app uses; commands read/write `AR_job_queue`,
`AR_cron` and domain tables.

## Inputs / Outputs

- **Input:** CLI arguments (command name + options).
- **Output:** console/log output; side effects on the DB and external services.

## Side effects

Running `processjobs` drains the job queue and triggers emails/push/printing/refunds. Cron
commands mutate order/points/subscription state.

## Security considerations

- Cron/task execution should be guarded by `CRON_KEY` when triggered over HTTP
  (`Task*Controller`); the CLI path relies on shell access.
- Same secret-handling rules as the web config: credentials from `k-config.php` only.

## Error handling

Errors are written to the file log route (`application.log`).

## Known limitations / Future improvements

- The module list is duplicated between `console.php` and `front_main.php`; keep them in
  sync when enabling gateways. Consider extracting a shared modules include.

## Related files

- [`front_main.php.md`](front_main.php.md), [`params.php.md`](params.php.md)
- [`protected/commands/AGENTS.md`](../protected/commands/AGENTS.md),
  [`protected/components/AGENTS.md`](../protected/components/AGENTS.md) (job classes)
