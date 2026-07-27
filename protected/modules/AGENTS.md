# protected/modules — Payment Gateways & Feature Add-ons

## Purpose

Self-contained Yii modules. Two kinds: **payment gateways** (the majority) and **packaged
feature add-ons** (mobile apps, driver, kitchen, POS, location search, menu clone). Modules
are registered in `config/front_main.php` (and `console.php`) `modules` arrays. 24 module
directories on disk; ~60 gateways are enabled in config (some share a directory).

## Ownership

Governs `protected/modules/`. Inherits rules from `protected/AGENTS.md`.

## Layout of a gateway module

Typical structure (see `touchpay/`, `stripe/`, `paypal/`, `razorpay/`, `mtn/`,
`mercadopago/`, `vivawallet/`, `cinetpay/`, `digital_wallet/`, `paydelivery/`, `cod/`, `bank/`):

- `XxxModule.php` — module bootstrap.
- `controllers/` — `ApiController` (app-facing), `ApiappController`, `WebhookController`
  (provider callbacks; CSRF-exempt, must verify signatures).
- `components/` — `PaymentXxx.php` (gateway client: token, checkout, refund, verify).
- `filesupdate/` — versioned installers: `vNNN.sql`, `vNNN-update.sql`, and a
  `front_main.txt` snippet showing the config/route lines the module needs.

## Feature add-on modules

`MobileModules`, `MobileVue`, `SingleAppModules`, `SingleModules`, `DriverModules`,
`KitchenModules`, `TableSideModules`, `MerchantModules`, `MenuCloneModules`,
`LocationBasedModules`. These package mobile builds and admin features; some ship `files.zip`
distributables and `filesupdate/*.sql`.

## Local Contracts

- Adding/enabling a gateway requires: register it in the `modules` array of
  `config/front_main.php`, add its API/webhook route prefixes to `noCsrfValidationRoutes`,
  and apply its `filesupdate/*.sql`.
- Gateway credentials (client id/secret, API keys) are configured per-merchant in the DB /
  admin, **not** hardcoded in module code. Do not commit real keys into module files.
- Webhook controllers must verify the provider's signature/HMAC before acting; they are
  unauthenticated and CSRF-exempt.
- Payment components must route through / log via `CPayments` / `CPaymentLogger` for
  consistent, redacted transaction records.
- `cinetpay/` (customer-facing label **Mobile Money**: Orange/MTN/Moov/Wave via the
  CinetPay hosted checkout) is the reference for a fully server-side redirect gateway:
  verify-first webhook (403 forge / 503 gateway-down), idempotent paid transition, unique
  reference per attempt, credentials sanitized out of `fetchpaymentmethod`. Unit tests:
  `php protected/modules/cinetpay/tests/run-tests.php`. Doc:
  [`/docs/cinetpay-mobile-money.md`](../../docs/cinetpay-mobile-money.md).

## Do-not-touch-blindly

- `*.zip` distributables and `filesupdate/*.sql` installers are release artifacts; regenerate
  them through the module's own build/update process, don't hand-edit.
- `MobileVue/` is a bundled copy of a Quasar app source tree (its own `package.json`); its
  `node_modules/` is git-ignored and not present.

## Related documentation

- Money services shared by gateways: [`../components/AGENTS.md`](../components/AGENTS.md)
- Routing / CSRF exemptions: [`../../docs/front_main.php.md`](../../docs/front_main.php.md)
- Reference docs: [`/docs`](../../docs/README.md)
