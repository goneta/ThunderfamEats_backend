# protected/commands — Console Commands (Queue Worker + Cron)

## Purpose

Yii console commands run from the CLI app (`config/console.php`). They drain the async job
queue and run scheduled maintenance. 14 commands.

## Ownership

Governs `protected/commands/`. Inherits rules from `protected/AGENTS.md`.

## Commands

- **`ProcessJobsCommand`** (`processjobs`, registered in `console.php` `commandMap`) — the
  queue worker. Pulls rows from `AR_job_queue` and dispatches to the matching
  `components/jobs/*` class. This is the backbone of all async work.
- **Scheduled maintenance** (run via system cron / scheduler, typically guarded by
  `CRON_KEY`): `ProcessStatusupdateCommand`, `ProcessTrackorderCommand`,
  `ProcessPauseordersCommand`, `AssignScheduleorderCommand`, `RetryAssignmentCommand`,
  `ProcessAssignedtimeoutCommand`, `ResumeItemsCommand`, `SponsoredExpiryCommand`,
  `ProcessBanksubscriptionCommand`, `ProcessMonthlypointsCommand`,
  `ProcessPointsRanksCommand`, `ProcessPointsExpiryCommand`, `ProcessPointsExpiry2Command`.

## Local Contracts

- Commands orchestrate; the actual work stays in `components/`, `jobs/` and models.
- Console runs under `config/console.php`, which still requires the root `k-config.php`
  constants (DB, `CRON_KEY`) — the CLI must be invoked from a context where those load.
- Web-triggered equivalents (`Task*Controller`) exist for hosts without shell cron; both
  paths must stay behaviourally consistent.

## Operational notes

- The queue worker is meant to run continuously or on a short cron interval; if it stops,
  emails/push/printing/refunds silently back up in `AR_job_queue`.
- Schedule and last-run state is tracked in `AR_cron`.

## Related documentation

- Job classes: [`../components/AGENTS.md`](../components/AGENTS.md)
- Console config: [`../../docs/console.php.md`](../../docs/console.php.md)
- Reference docs: [`/docs`](../../docs/README.md)
