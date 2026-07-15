# protected/components — Services, Helpers, Jobs

## Purpose

Reusable service and helper classes (`C*` and identity/widget classes) that hold the app's
business logic, plus the async job classes under `jobs/`. 72 component classes + 46 jobs.
Controllers should be thin and delegate here.

## Ownership

Governs `protected/components/` including `jobs/` and `views/`. Inherits rules from
`protected/AGENTS.md`. (How jobs are *run* is documented in `../commands/AGENTS.md`.)

## Component groups

- **Payments / money:** `CPayments`, `CPaymentLogger`, `CCommission`, `CTips`, `CPlan`,
  `CMulticurrency`, `CCurrency`, `CMerchantInvoice`, `CPromos`.
- **Orders / fulfilment:** `COrders`, `CCart`, `CCheckout`, `CBooking`, `CTrackingOrder`,
  `CScrapmenu`, `Citems`, `BItemInstant`.
- **Merchant / catalog:** `CMerchantListingV1`, `CMerchantMenu`, `MerchantMenuHelper`,
  `CMerchantSignup`, `CSavedStore`, `CFeaturedLocation`, `CCuisine`, `CServices`, `CReviews`.
- **Identity / auth:** `App*Identity` (User/Merchant/Driver/Kitchen/Tableside),
  `Customer*Identity*`, `Merchant*Identity*`, `DriverIdentity`, `WebUserCustomer`,
  `JWTwrapper`, `CSocialLogin`, `CRecaptcha`.
- **Messaging / notifications:** `CFirebase`, `CNotifications`, `CSMSsender`, `CEmailer`,
  `CTemplates`.
- **Geo / maps:** `CMaps`, `MapSdk`, `Geo_geolocationdb`, `LocationSet`, `ClocationCountry`.
- **Media / SEO / UI:** `CImageUploader`, `CSeo`, `CNavs`, `CComponentsManager`,
  `AssetsFrontBundle`, `Widget*` (site logo, menus, currency/lang selectors, cookie consent,
  Facebook pixel, carousels).
- **Infra:** `RedisHelper` (cache backend selection), `HttpRequest` (custom request with CSRF
  route exemptions), `CustomUrlRule`, `CustomFields`, `CComponentsManager`.

## `jobs/` — async work

Each `jobs/*` class is a unit of work pulled from `AR_job_queue` and executed by the
`processjobs` command. Covers post-purchase flows, order status fan-out, refunds/reversals,
emails, WhatsApp/SMS, printing, kitchen dispatch, subscriptions, promos, points. Keep jobs
idempotent where possible — the worker may retry.

## Local Contracts

- Business rules live here, not in controllers. Prefer extending an existing `C*` service.
- Side-effectful work (network calls, emails, push, printing) belongs in a job, enqueued via
  `AR_job_queue`, not run inline in a request.
- `.phps` siblings (e.g. `CCart.phps`, `CMerchantMenu.phps`) are source snapshots/backups —
  the live class is the `.php`. Do not wire code to `.phps`.
- Never log tokens, card data or PII (payment loggers must redact).

## Security considerations

- `JWTwrapper`, `CRecaptcha`, `CSocialLogin` and the `*Identity` classes are the auth
  perimeter — review carefully; a mistake here is an authentication bypass.
- `CImageUploader` handles untrusted uploads — keep type/size validation (`params.php`
  `Helper_imageType`, `Helper_maxSize`) intact.

## Related documentation

- Handlers: [`../controllers/AGENTS.md`](../controllers/AGENTS.md)
- Data: [`../models/AGENTS.md`](../models/AGENTS.md)
- Running jobs: [`../commands/AGENTS.md`](../commands/AGENTS.md)
- Reference docs: [`/docs`](../../docs/README.md)
