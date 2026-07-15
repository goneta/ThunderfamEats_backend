# index.php

Reference doc for [`/index.php`](../index.php). Owning contract: [`/AGENTS.md`](../AGENTS.md).

## File purpose

Front controller and bootstrap for the customer-facing / API application. Every non-static
HTTP request to the site root is routed here by the web server (`.htaccess` / `web.config`
rewrite rules).

## Responsibilities

- Locate and require the bundled Yii framework (`framework/yiilite.php`).
- Load environment secrets and constants from `k-config.php` **before** anything else.
- Load the web application config (`protected/config/front_main.php`).
- Set the `YII_DEBUG` flag.
- Create and run the Yii web application.

## Architecture overview

Thin bootstrap. All routing, module wiring and component setup live in
`protected/config/front_main.php`; all logic lives in controllers/components. Boot order:

1. `require k-config.php` → defines `DB_*`, `BACKOFFICE_FOLDER`, `CRON_KEY`, `DEMO_*`.
2. `require framework/yiilite.php` → the Yii runtime.
3. `Yii::createWebApplication($config)->run()` with `front_main.php`.

## Entry points

- Web server → `index.php` (the only front-app PHP entry). The admin app has its own
  `backoffice/index.php`.

## Public functions/classes

None — procedural bootstrap script.

## Dependencies

- `k-config.php` (git-ignored; create from `k-config.sample.php`).
- `framework/yiilite.php` (bundled Yii 1.1).
- `protected/config/front_main.php`.

## Database interactions

None directly; the DB connection is configured in `front_main.php` from the `DB_*` constants
that `k-config.php` defines.

## Inputs / Outputs

- **Input:** the raw HTTP request (via the web server).
- **Output:** whatever the resolved controller/action returns (HTML page or JSON envelope).

## Side effects

Boots the full framework and application; opens the DB connection lazily on first use.

## Security considerations

- `YII_DEBUG` is currently set to `true`. **In production this must be `false`** — debug mode
  leaks stack traces and file paths (already visible in `protected/runtime/application.log`).
- Do not add secrets here; they belong in the git-ignored `k-config.php`.

## Error handling

Errors bubble to Yii's error handler, configured in `front_main.php`
(`errorHandler.errorAction = store/pagenotfound`).

## Performance considerations

Uses `yiilite.php` (the concatenated, optimized single-file build) rather than `yii.php`.

## Known limitations

- Hardcoded debug flag; no per-environment switch beyond editing the file.

## Future improvements

- Drive `YII_DEBUG` from an environment variable / `k-config.php` constant.

## Related files

- [`k-config.sample.php.md`](k-config.sample.php.md)
- [`front_main.php.md`](front_main.php.md)
- `backoffice/index.php` (admin bootstrap; uses `framework/yii.php`).
