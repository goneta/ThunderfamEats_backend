# protected/config/front_main.php

Reference doc for [`/protected/config/front_main.php`](../protected/config/front_main.php).
Owning contract: [`protected/AGENTS.md`](../protected/AGENTS.md).

## File purpose

The main configuration array for the front / API web application. Returned to
`Yii::createWebApplication()` by `index.php`. Wires modules, application components, routing,
CSRF policy and the database connection.

## Responsibilities

- Declare path aliases: `backend` / `backend_webroot` → `backoffice/protected`,
  `upload_dir`, `modules_dir`, `home_dir`.
- Preload the Yii `RedisHelper` and import model/component/controller/vendor/extension
  namespaces (`application.*`, `backend.*`).
- Register ~60 payment gateway + feature **modules**.
- Configure **components**:
  - `cache` — `CRedisCache` when `RedisHelper::isRedisAvailable()`, else `CFileCache`.
  - `messages` — DB-backed translations (`CDbMessageSource`).
  - `request` — custom `HttpRequest` with CSRF + cookie validation and a
    `noCsrfValidationRoutes` allow-list for APIs/webhooks.
  - `user` / `merchant` / `driver` — three web-user identities with auto-login.
  - `db` — MySQL PDO connection built from `DB_*` constants; disables
    `ONLY_FULL_GROUP_BY`; schema caching 60s.
  - `urlManager` — path-format URLs, `showScriptName=false`, plus a DB-driven
    `CustomUrlRule` and explicit route rules.
  - `errorHandler` — routes errors to `store/pagenotfound`.
  - `log` — file log route (error/warning/info).
  - `input` — `CmsInput` with `cleanPost`/`cleanGet`.
- Merge in `params.php`.

## Entry points

Loaded once per request by `index.php`. Not called directly.

## Dependencies

- Constants from `k-config.php` (`DB_*`, `BACKOFFICE_FOLDER`, `KMRS_DEFAULT_LANGUAGE`).
- `protected/components/RedisHelper.php`, `HttpRequest.php`, `CustomUrlRule.php`, `CmsInput`.
- `protected/config/params.php`.

## Database interactions

Defines the single shared `db` connection (table prefix `st_`). No queries itself.

## API endpoints / routing

- URL format is `path`; most traffic maps via `<controller>/<action>` rules and the DB-backed
  `CustomUrlRule` (SEO slugs for restaurants/offers/pages).
- **CSRF-exempt route prefixes** live in `request.noCsrfValidationRoutes` — every mobile API
  and payment webhook (`interface/*`, `pv1/*`, `payv1/*`, `driver/*`, `chatapi/*`,
  `stripe/webhooks`, `paypal/api*`, `touchpay/*`, etc.). **Add new API/webhook prefixes here**
  or requests will be rejected.

## Inputs / Outputs

- **Input:** none at load; consumed by the framework.
- **Output:** a configuration array.

## Side effects

Establishes app-wide behavior for the whole request lifecycle.

## Security considerations

- The CSRF allow-list is a security-sensitive surface: only genuinely
  token-authenticated/signature-verified routes belong there. Webhook actions must verify
  provider signatures because they are both CSRF-exempt and unauthenticated.
- DB credentials come from `k-config.php` — never inline them here.
- `.phps` sibling (`front_main.phps`) is a source snapshot; the live config is `.php`.

## Error handling

Delegated to Yii's error handler → `store/pagenotfound`.

## Performance considerations

Redis cache when available; schema caching (60s) reduces metadata queries.

## Known limitations

- Very large module list loaded on every request (minor overhead).
- Timezone hardcoded to `Asia/Manila`.

## Future improvements

- Externalize the module list / timezone per environment.

## Related files

- [`index.php.md`](index.php.md), [`console.php.md`](console.php.md),
  [`params.php.md`](params.php.md)
- [`protected/controllers/AGENTS.md`](../protected/controllers/AGENTS.md) (routes reference this)
- [`protected/modules/AGENTS.md`](../protected/modules/AGENTS.md) (module registration)
