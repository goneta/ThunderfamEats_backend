# protected/models — Data Layer (ActiveRecord)

## Purpose

Yii ActiveRecord classes that map database tables to PHP objects. All files are named
`AR_<entity>.php` and back the `st_`-prefixed MySQL tables (49 models).

## Ownership

Governs `protected/models/`. Inherits rules from `protected/AGENTS.md`.

## Conventions

- One class per table, `class AR_<entity> extends CActiveRecord` (or a project base AR).
- `tableName()` returns the logical table; the `st_` prefix comes from `DB_PREFIX` and is
  applied by Yii's `tablePrefix` / `{{table}}` expansion — do not hardcode `st_` in queries.
- Relations, validation rules and scopes live on the model, not in controllers.
- Backoffice models are importable in the front app via the `backend.models.*` import alias
  (see `config/front_main.php`); prefer the front model unless a backoffice-only table is
  needed.

## Key entities (representative)

- **Ordering:** `AR_ordernew`, `AR_ordernew_addons`, `AR_ordernew_attributes`,
  `AR_ordernew_history`, `AR_ordernew_additional_charge`, `AR_cart`, `AR_cart_addons`,
  `AR_cart_attributes`.
- **Customer:** `AR_clientsignup`, `AR_customer_login`, `AR_customer_autologin`,
  `AR_client_address(_location)`, `AR_client_cc`, `AR_client_meta`, `AR_client_booking`,
  `AR_client_payment_method`, `AR_favorites`, `AR_gpdr_request`.
- **Driver:** `AR_driver_login`, `AR_driver_location`, `AR_driver_payment_method`.
- **Kitchen / POS:** `AR_kitchen_order`.
- **Infra:** `AR_job_queue` (async queue), `AR_cron` (schedule state),
  `AR_map_places`, `AR_customer_request`, `AR_contact`, `AR_item_free_promo`.

## Local Contracts

- Never build SQL by concatenating request input; use AR conditions/params or the `q()`
  helper (`params.php`).
- Schema changes must ship as a versioned SQL installer (see module `filesupdate/*.sql`) and
  be recorded in [`CLAUDE.md`](../../CLAUDE.md) under Database Changes.

## Related documentation

- Handlers: [`../controllers/AGENTS.md`](../controllers/AGENTS.md)
- Services that read/write these models: [`../components/AGENTS.md`](../components/AGENTS.md)
- Reference docs: [`/docs`](../../docs/README.md)
