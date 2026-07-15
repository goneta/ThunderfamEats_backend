# protected/controllers — Request Handlers

## Purpose

Yii controllers that handle every HTTP request: storefront pages, mobile-app JSON APIs, and
payment webhooks. 40 controllers plus shared `*Common` base classes.

## Ownership

Governs `protected/controllers/` and its subfolders (`booking/`, `cashin/`, `chat/`,
`paymentapi/`, `plan/`). Inherits all rules from `protected/AGENTS.md`.

## Controller families

- **Web / storefront:** `StoreController`, `PageController`, `AccountController`,
  `MenuController`, `MerchantController`, `OrdersController`, `ReservationController`,
  `WalletController`, `SitemapController`, `PrintController`. Render `themes/karenderia_v2`
  views; CSRF-validated.
- **Customer / mobile API:** `InterfaceController` (largest surface — auth, locations,
  merchants, feed, addresses), `Pv1Controller`, `Payv1Controller`, `ApiController`,
  `ApiposController`, `ApitableController`, `ApikitchenController`, `ApilocationsController`,
  `ApisubscriptionsController`, `Apibooking(v2)Controller`.
- **Merchant / partner API:** `InterfacemerchantController`, `InterfacesubscriptionController`,
  `PartnerapiController`, `PartnerCommon`.
- **Driver API:** `DriverController`, `DeliveryboyController`, `DriverpaymentController`,
  `DriverCommon`, `ChatdriverController`.
- **Chat:** `ChatapiController`.
- **Async / cron entry (web-triggered):** `TaskController`, `TaskbookingController`,
  `TasksmsController`, `TaskinvoiceController`, `TaskexchangerateController`,
  `ScrapmenuController`.
- **Shared base classes:** `SiteCommon`, `InterfaceCommon`, `PartnerCommon`, `DriverCommon`
  — never routed directly; hold shared setup (auth, response helpers, DB bootstrap).

## Local Contracts

- API controllers return the `{ code, msg, details }` envelope; do not invent per-endpoint
  shapes.
- Any API/webhook action must have its route prefix listed in
  `config/front_main.php` → `noCsrfValidationRoutes`, or CSRF validation will reject it.
- Keep action bodies thin: delegate to components (`C*`) and enqueue side effects as jobs.
- Read inputs via Yii request (`Yii::app()->request`) with the sanitizing `CmsInput`
  (`cleanPost`/`cleanGet` are on); still bind parameters in SQL.

## Security considerations

- Webhook controllers (e.g. module `*/WebhookController`, `stripe/webhooks`) are CSRF-exempt
  and unauthenticated — they must verify the provider's signature before trusting the body.
- Do not echo secrets or full gateway responses to clients.

## Do-not-touch-blindly

- `InterfaceController.php` (~11k lines) and `ApiController.php` (~7k lines) are large,
  high-traffic API surfaces. Change one action at a time and preserve the envelope.
- `*Common.php` base classes affect every subclass — a change ripples across many endpoints.

## Related documentation

- Data: [`../models/AGENTS.md`](../models/AGENTS.md)
- Services: [`../components/AGENTS.md`](../components/AGENTS.md)
- Routing/CSRF config: [`../../docs/front_main.php.md`](../../docs/front_main.php.md)
- Reference docs: [`/docs`](../../docs/README.md)
