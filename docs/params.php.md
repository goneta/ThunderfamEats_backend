# protected/config/params.php

Reference doc for [`/protected/config/params.php`](../protected/config/params.php).
Owning contract: [`protected/AGENTS.md`](../protected/AGENTS.md).

## File purpose

Application parameters plus a set of global helper functions. Returned as the `params` array
merged into both `front_main.php` and `console.php`, and accessed at runtime via
`Yii::app()->params['...']`.

## Responsibilities

- Define validation/message string constants (`Helper_field_*`, `Helper_password_*`, etc.).
- Define cache-duration and upload constraints (`CACHE_*_DURATION`, `Helper_maxSize`,
  `Helper_imageType`).
- Define the app custom URL schemes and the `params` array: title, theme (`karenderia_v2`),
  list limits, image sizes/driver (GD), account types, realtime channel names, push config,
  booking/tableside prefixes, and a `location_addon_identity` licensing token.
- Provide global helper functions used across the codebase.

## Public functions

| Function | Purpose |
|----------|---------|
| `t($text, $args, $language='front')` | Translation shortcut over `Yii::t()` |
| `q($data)` | Safely quote a value for SQL via `Yii::app()->db->quoteValue()` (returns `NULL` for null) |
| `dump($data)` | Debug pretty-printer (`<pre>print_r</pre>`) |
| `websiteUrl()` | Absolute base URL |
| `websiteDomain()` | Request host info |

## Dependencies

Relies on Yii being booted (uses `Yii::app()`, `Yii::t()`). `DEMO_MERCHANT` is defaulted here
if not already defined by `k-config.php`.

## Inputs / Outputs

- **Input:** none at load.
- **Output:** the `params` array + registered global functions.

## Side effects

Declares global functions in the PHP namespace; defines constants.

## Security considerations

- `q()` is the project's SQL-quoting helper — use it (or AR parameter binding) instead of
  concatenating raw input into queries.
- `dump()` echoes data directly; never leave `dump()` calls in production paths (info leak).
- `location_addon_identity` is a licensing/addon token, not a user credential, but treat
  config tokens as sensitive.

## Known limitations

- Mixes constants, config data and global functions in one file (legacy KMRS structure).

## Future improvements

- Split helper functions into an autoloaded component; move tunables to per-environment config.

## Related files

- [`front_main.php.md`](front_main.php.md), [`console.php.md`](console.php.md)
- [`protected/models/AGENTS.md`](../protected/models/AGENTS.md) (uses `q()` for safe SQL)
