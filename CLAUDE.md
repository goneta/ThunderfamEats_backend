# CLAUDE.md — ThunderfamEats Backend Engineering Journal

Persistent engineering journal and AI working memory for the ThunderfamEats Backend.
Read this first, then the DOX chain ([`AGENTS.md`](AGENTS.md) and children) and the reference
docs in [`docs/`](docs/README.md). **Append** a dated entry to the Change Log on every change;
never overwrite prior entries.

---

## Project overview

ThunderfamEats Backend is a **Yii 1.x (PHP)** application based on the *Karenderia Multiple
Restaurant System (KMRS)*, customized for ThunderfamEats. It powers a multi-restaurant food
ordering / delivery / booking platform and serves three surfaces from one repo:

1. **Storefront + customer web** — themed site (`themes/karenderia_v2`) rendered by the front
   app (`protected/`), entry `index.php`.
2. **JSON APIs** — for the customer, driver, merchant, kitchen and POS mobile apps, plus
   payment webhooks.
3. **Back office** — a second bundled Yii app (`backoffice/`) for administration.

## Repository structure

```
/                     front app bootstrap (index.php), web-server glue, k-config.*
  protected/          front/API application (controllers, models, components, jobs,
                      commands, modules, config, extensions, vendor)
  backoffice/         admin application (own controllers/models/migrations/config)
  framework/          bundled Yii 1.1 (vendored; do not edit)
  themes/             karenderia_v2 views + assets (front view layer)
  assets/             published web assets
  docs/               per-file reference docs + master index
  upload/  *ignored*  user media (also historically held leaked service-account keys)
  backup1/ *ignored*  local full-copy backup
```
Full map: [`AGENTS.md`](AGENTS.md) → Repository Map. Directory contracts: the `AGENTS.md` in
each area. Per-file docs: [`docs/README.md`](docs/README.md).

## Coding standards

- Yii 1.x conventions: controllers thin, logic in components (`C*`) and models (`AR_*`).
- Table prefix `st_` via `DB_PREFIX`; never hardcode the prefix, use AR / `{{table}}`.
- SQL: AR conditions or the `q()` helper / parameter binding — never concatenate request input.
- Side effects (email/push/print/refund/status fan-out) run as `AR_job_queue` jobs, not inline.
- User-facing strings via `t()` / `Yii::t()`.
- Do not edit `framework/` or vendored `vendor/` / `extensions/` code.
- Never log or commit secrets, tokens, card data or PII.

## Architecture decisions

- **Single shared DB** across front and back office; the front app imports `backend.*`
  aliases into `backoffice/protected`.
- **Cache:** Redis when available (`RedisHelper`), file cache otherwise — chosen at config
  time in `front_main.php`.
- **Async model:** DB-backed queue (`AR_job_queue`) drained by the `processjobs` console
  command; scheduled work via `commands/Process*` (or web `Task*Controller`, guarded by
  `CRON_KEY`, for hosts without shell cron).
- **Payments:** one module per gateway under `protected/modules/*`; credentials stored
  per-merchant in the DB, not in code. Webhooks are CSRF-exempt and must verify signatures.
- **Vendored framework:** Yii and third-party libs are committed (no Composer at root) so the
  repo is directly deployable.

## Development guidelines

- Before editing, read the DOX chain from root to the target path (see `AGENTS.md`).
- New API/webhook routes must be added to `noCsrfValidationRoutes` in `front_main.php`.
- Verify by exercising the affected page/endpoint against a local DB (seed from
  `karenderia.sql`) and checking `protected/runtime/application.log`. No automated tests exist.
- Update the relevant `docs/*.md`, the owning `AGENTS.md`, and append a Change Log entry here.

## Security considerations

- `k-config.php` (DB password + `CRON_KEY`) is **git-ignored**; use `k-config.sample.php` as
  the template. See [`docs/k-config.sample.php.md`](docs/k-config.sample.php.md).
- `.gitignore` blocks `.env*` (except `*.sample`), `*.pem`/`*.key`/`*.p12`/`*.keystore`,
  `*firebase-adminsdk*.json`, `google-services.json`, `karenderia.sql`, `upload/`, backups,
  `protected/runtime/*` and `phpinfo.php`. Verify new config with `git check-ignore`.
- `YII_DEBUG` is `true` in both `index.php` files — must be `false` in production.
- Firebase **service-account private keys** exist under the (ignored) `upload/` and `backup1/`
  trees. They are excluded from git but remain on disk and were exposed via a public upload
  path — **rotate them** (see Known issues).

## Known issues / Technical debt

- **Exposed secrets (rotate):** the working tree's live `k-config.php` DB password and
  `CRON_KEY`, plus Firebase admin-SDK JSON keys under `upload/`, were present in plaintext.
  Excluded from git, but should be rotated since they lived in a servable path.
- `YII_DEBUG = true` in production entry points (info disclosure).
- `phpinfo.php` present at repo root (info disclosure) — excluded from git; delete from the
  server.
- No automated tests.
- No Composer/dependency manager at root; framework and libs are vendored, so the blanket
  `*.pem`/`*.key` ignore also excludes vendored CA bundles (e.g. razorpay `cacert.pem`) that
  some HTTP clients expect — re-add per-library if SSL verification fails.
- Monolithic controllers (`InterfaceController` ~11k lines, `ApiController` ~7k lines).
- Module list duplicated between `front_main.php` and `console.php`.

## Completed work

- DOX documentation tree initialized (root + child `AGENTS.md`, `docs/` reference layer).
- Security audit + `.gitignore` + sanitized `k-config.sample.php`; initial repository import.

## Current work in progress

- None open. See Change Log for the latest session.

## Pending tasks / Future roadmap

- Rotate all exposed credentials (DB, `CRON_KEY`, Firebase service accounts).
- Set `YII_DEBUG = false` and remove `phpinfo.php` on the server.
- Expand per-file `docs/*.md` coverage to remaining key controllers/components.
- Introduce automated tests and, ideally, Composer-managed dependencies.
- Consider Git LFS or a separate store for large media if any binaries must be versioned.

## Database changes

- None made in code. Schema originates from `karenderia.sql` (ignored dump); module schema
  installers live in `protected/modules/*/filesupdate/*.sql`; admin migrations in
  `backoffice/protected/migrations/`.

## API changes

- None. API surface documented in
  [`docs/InterfaceController.php.md`](docs/InterfaceController.php.md) and
  [`protected/controllers/AGENTS.md`](protected/controllers/AGENTS.md).

## Deployment notes

- Copy `k-config.sample.php` → `k-config.php` and fill real values on each environment.
- Ensure `framework/` and vendored libs are present (they are committed).
- Run the `processjobs` worker continuously / on a short cron interval, plus the scheduled
  `Process*` commands. Set `YII_DEBUG = false`.

---

## Change Log

### 2026-07-22 — Customer-app chat auth + AI assistant gateway (InterfaceController)

**What was changed**
- Added two customer-authenticated endpoints to `protected/controllers/InterfaceController.php`:
  - `actionfirebaseCustomToken` (+ private `loadFirebaseServiceAccount`) — `POST /interface/firebaseCustomToken`. Mints a Firebase **custom token** (RS256 JWT via the already-vendored `Firebase\JWT\JWT`, signed with the service-account key) whose `uid` = the authenticated customer's `client_uuid`. Fulfils the app contract in `MobileVue/docs/backend/firebase-custom-token.md` and unblocks participant-only Firestore chat rules. Config: `FIREBASE_SA_JSON_PATH` in `k-config.php`.
  - `actionaichat` (+ private `aiCall`) — `POST /interface/aichat`. Server-side, **read-only** AI assistant for the customer app. Builds a system prompt (app description + customer first name + locale + optional app-supplied read-only `context`), calls the configured LLM (Anthropic Messages API in v1) via cURL with a **server-side** key, returns `{ details:{ reply } }`. The prompt forbids write/order/payment claims; no DB writes. Config: `TFE_AI_PROVIDER` / `TFE_AI_MODEL` / `TFE_AI_API_KEY`.
- Registered both actions under `verifyCustomer` in `protected/controllers/InterfaceCommon.php` `accessRules()`.
- Documented the new `k-config.php` defines in `k-config.sample.php` (commented; secrets stay in the git-ignored `k-config.php` / service-account JSON).
- New reference doc: `docs/ai-gateway-and-firebase-token.md` (setup, config, curl tests, security, owner verification checklist).

**Notes / not done here**
- No route/CSRF change: `interface/*` is already in `front_main.php` → `noCsrfValidationRoutes`.
- Both endpoints are **disabled until configured** and fail closed (non-`1` code); the app degrades gracefully (deterministic read-only assistant; chat connect-error).
- Authored without a local PHP CLI — `php -l` and a live curl test against a staging DB are on the owner's verification checklist (see the doc). Static brace/paren balance was checked. Not deployed.

### 2026-07-15 — Repository security hardening + DOX documentation initialization

**What was changed**
- Performed a full security audit of the untracked codebase before the first real commit
  (previously only `README.md` was tracked).
- Added a comprehensive [`.gitignore`](.gitignore) covering secrets, DB dumps, user uploads,
  backups, runtime/cache, OS/IDE files, `node_modules`, and diagnostic endpoints.
- Added sanitized [`k-config.sample.php`](k-config.sample.php) template (placeholders only);
  kept the real `k-config.php` git-ignored.
- Initialized the DOX documentation tree: expanded the root [`AGENTS.md`](AGENTS.md) with
  project overview / repo map / security contract / child index; created child `AGENTS.md`
  for `protected/`, `protected/controllers/`, `protected/models/`, `protected/components/`,
  `protected/commands/`, `protected/modules/`, and `backoffice/`.
- Created the reference-doc layer under [`docs/`](docs/README.md): master index plus per-file
  docs for `index.php`, `k-config.sample.php`, `front_main.php`, `console.php`, `params.php`,
  and `InterfaceController.php`.
- Created this `CLAUDE.md`.

**Why**
- The repo was about to receive its first full import; live DB credentials, a `CRON_KEY`,
  Firebase service-account private keys, a 400 MB `upload/` tree, DB dumps and local backups
  were all untracked and at risk of being committed and pushed publicly.
- The user requested the same DOX documentation architecture used for the ThunderfamEats
  Customer App, to serve as long-term technical documentation.

**Files modified / added**
- Added: `.gitignore`, `k-config.sample.php`, `CLAUDE.md`, `docs/README.md`,
  `docs/index.php.md`, `docs/k-config.sample.php.md`, `docs/front_main.php.md`,
  `docs/console.php.md`, `docs/params.php.md`, `docs/InterfaceController.php.md`,
  `protected/AGENTS.md`, `protected/controllers/AGENTS.md`, `protected/models/AGENTS.md`,
  `protected/components/AGENTS.md`, `protected/commands/AGENTS.md`,
  `protected/modules/AGENTS.md`, `backoffice/AGENTS.md`.
- Modified: `AGENTS.md` (replaced the "not yet indexed" placeholder with the real index).

**Architectural decisions**
- Keep the vendored `framework/` (29 MB) and library trees committed (no Composer at root; the
  repo must stay directly deployable).
- Use a security-first blanket ignore for `*.pem`/`*.key` etc., accepting that a few vendored
  public CA bundles are also excluded (documented as re-addable if needed).
- Keep module `filesupdate/*.sql` schema installers versioned (DDL, not data dumps).

**Security improvements**
- No secrets, keys, dumps, uploads or backups are staged for commit (verified via
  `git check-ignore` and a pattern scan of the staged file list).

**Remaining work / Suggested next steps**
- **Rotate** the DB password, `CRON_KEY`, and all Firebase service-account keys that were
  present in plaintext on disk / a servable path.
- Set `YII_DEBUG = false` and remove `phpinfo.php` from the server.
- Continue expanding `docs/*.md` for remaining key controllers/components and back-office.
- Add automated tests; evaluate migrating dependencies to Composer.

### 2026-07-16 — Front-end UI redesign (home + merchant signup, light/dark)

**Objectives**
- Redesign the public storefront **home page** and the **merchant signup page** to match the
  provided mockups (dark + light mode), purely presentationally — no change to routes, APIs,
  controllers, models, business logic, auth, payments, validations or the registration flow.

**What was changed**
- Added a redesign stylesheet, `themes/karenderia_v2/assets/css/custom.css` (previously empty,
  already enqueued **last** in the `front-css` bundle so it overrides `style.css` without
  editing it). It defines `--tf-*` design tokens, light/dark theming, and restyles nav, hero,
  advantage cards, promo cards, join banner, app section, footer and the merchant form/hero.
- Light/dark theming via `data-theme` on `<html>`: a no-FOUC init script + a toggle handler in
  `views/layouts/main-layout.php`, a toggle button (`#tf-theme-toggle`) in
  `views/layouts/top-nav.php`, persisted to `localStorage` (`tf-theme`), defaulting to
  `prefers-color-scheme`.
- `views/store/index.php`: restructured the hero into a two-column layout (heading + subtitle +
  existing "Locate Your Location" search component + `full-header@2x.png` illustration); added a
  "Nos avantages exclusifs" section title; rebuilt the three advantage cards
  (`.tf-advantages`) using `addons-*_new.png`, removing broken inline styles. Added a `tf-home`
  page-scope marker.
- `views/merchant/merchant-signup.php`: added a hero banner ("Grow your business…" + Register
  Now), wrapped the existing Vue form as a floating card, and added a new "How it works"
  3-step section. Added a `tf-merchant` page-scope marker. All real form fields (store name,
  address autocomplete, membership, services, recaptcha, submit) were preserved unchanged.

**UI / architectural decisions**
- CSS-override layer (`custom.css`) instead of touching `style.css` → additive, low-risk,
  trivially revertible.
- Page-scope classes (`tf-home` / `tf-merchant`) added to `<html>` so deep restyles don't leak
  to other pages; nav/footer theming kept global for consistency.
- Reused existing theme illustrations (`full-header`, `addons-*_new`, `benefits-*`, `register`)
  and the already-bundled `zmdi` icon font — no new assets committed.
- Kept the merchant form's real functional fields rather than the mockup's simplified fields,
  to preserve the registration flow.

**Verification**
- No PHP runtime available locally; validated via a throwaway static preview served over
  `python -m http.server` and inspected in-browser: confirmed correct DOM/section order and
  that `custom.css` computes correctly in **both** light and dark (tokens flip, green accent
  `#32b268`, 3-col advantage/steps grids, themed nav/footer/form card). Preview file removed
  before commit. Functional markup (Vue directives, form ids, `t()` strings, route URLs) left
  byte-identical.

**Files modified / added**
- Added: `themes/karenderia_v2/assets/css/custom.css`, `themes/karenderia_v2/AGENTS.md`,
  `docs/custom.css.md`.
- Modified: `themes/karenderia_v2/views/layouts/main-layout.php`,
  `themes/karenderia_v2/views/layouts/top-nav.php`,
  `themes/karenderia_v2/views/store/index.php`,
  `themes/karenderia_v2/views/merchant/merchant-signup.php`,
  `AGENTS.md` (child index), `docs/README.md` (index).

**Known limitations / future improvements**
- Deep page theming is scoped to home + signup; inner pages keep the legacy look but inherit
  the themed nav/footer. Extend token theming to restaurants/menu/checkout next.
- New display strings (hero copy, "How it works") use `t()` source strings; add translation-DB
  entries for full non-English rendering.

### 2026-07-23 — Extend redesign to restaurants/feed page + fix upload/.htaccess (Apache 2.4)

**Context**
- After the home + merchant redesign, the running local site (`thunderfameats.local`, WAMP,
  DocumentRoot = this repo) appeared "not redesigned." Diagnosis: the server *was* serving the
  new design (verified `GET /` and `/merchant/signup` return 200 with the redesign markup and
  `custom.css` loads as 200/text/css). The perceived issue was client-side: (1) browser cache,
  and (2) `StoreController::actionIndex` redirects `/` → `/store/restaurants` once a
  `kmrs_local_id` location cookie is set, and that results page had not been redesigned.

**What was changed**
- **Redesign extended to `/store/restaurants`** (`themes/karenderia_v2/views/store/feed.php`):
  added a `tf-feed` page-scope marker and wrapped each result column's content in a
  `.tf-store-card` div. Added a `12b. Restaurants / feed` section to `custom.css` theming the
  filter sidebar (as a card), result cards, "Show more" button, fast-delivery banner and page
  background — all via the existing `--tf-*` tokens, light/dark. No Vue directives, ids or
  routes changed.
- **Fixed `upload/.htaccess`** (git-ignored, server-local): replaced Apache 2.2 `deny from all`
  (which Apache 2.4 rejects → HTTP 500 on every `/upload/*` request, breaking user-uploaded
  images) with `Require all denied` wrapped in `mod_authz_core` / fallback blocks. Verified:
  an uploaded image now returns 200 (was 500) and a `.php` in `upload/` returns 403 (blocked).

**Verification**
- `feed.php` parses cleanly (a `/store/feed` request executed to line 41 before a pre-existing
  `Undefined variable $tabs_suggestion` runtime notice — that route, `actionFeed`, omits the
  vars `actionRestaurants` passes; unrelated to this change). Div balance confirmed (1 wrapper
  open/close). `custom.css` feed rules validated in a throwaway static preview: cards get
  14px radius + border + shadow and the sidebar becomes a card in both light and dark
  (tokens flip body `#fff`/`#0e0f11`, card `#fff`/`#1a1c1f`). Preview removed before commit.

**Files modified / added**
- Modified: `themes/karenderia_v2/views/store/feed.php`,
  `themes/karenderia_v2/assets/css/custom.css`, `themes/karenderia_v2/AGENTS.md`,
  `docs/custom.css.md`, `CLAUDE.md`.
- Server-local (git-ignored, not committed): `upload/.htaccess`.

**Known limitations / next steps**
- The location-mode results view (`feed-locations.php`) is not themed (site runs address mode).
- Menu / cart / checkout pages still use the legacy look; extend `--tf-*` theming there next.

### 2026-07-24 — New home hero (centered) + extend theme to menu/cart/checkout/location

**Objectives**
- Rework the home hero to match a second set of mockups (a full-bleed hero with a **centered**
  translucent "Locate Your Location" pill + subtitle, and **filled colour** advantage cards),
  in light and dark; and extend the `--tf-*` theming to the remaining customer pages
  (menu, cart, checkout, location-mode). Presentational only.

**What was changed**
- **Home hero redesign** (`views/store/index.php` + `custom.css` §5): replaced the earlier
  two-column hero (heading left / illustration right) with a centred, full-bleed band
  `.tf-hero.tf-hero-centered` — a themed gradient (blue in light, deep gradient in dark), the
  isometric `full-header@2x.png` bleeding off the right (`.tf-hero-illustration`), and centred
  content (`.tf-hero-center`) with the existing `component-auto-complete` styled as a
  translucent pill plus a subtitle ("Your favorite services, delivered fast"). Removed the big
  `.tf-hero-title` heading (mockups have none). The `component-auto-complete` is byte-identical.
- **Filled advantage cards** (`custom.css` §7): `.tf-adv-1/2/3` are now solid red/yellow/blue
  with white text and the illustration filling the top — the `addons-*_new.png` art is drawn
  for those colours. (Markup unchanged from the previous entry.)
- **Menu / cart / checkout / location theming** (`custom.css` §12c + page markers): added
  `tf-menu` (`store/menu.php`), `tf-checkout` (`account/checkout.php`) and `tf-locations`
  (`store/location_index.php`, `store/feed-locations.php`) `<html>` markers, and rules theming
  page backgrounds, headings/text, Bootstrap `.card`/element-plus `.el-card` panels, the menu
  category sidebar (`.sticky-sidebar`/`.menu-category`), form controls, and the location-mode
  sidebar/results — all via the existing tokens, light/dark. No structural markup changes on
  these pages.

**Verification**
- Static preview (served via `python -m http.server`, removed before commit) screenshotted in
  both themes: light hero = blue gradient + centred translucent pill + subtitle + isometric
  bleed; dark hero = deep gradient equivalent; advantage cards render as filled red/yellow/blue
  with white copy; responsive collapse confirmed. The live WAMP host was offline at commit time,
  so live-server verification is on the owner's checklist (hard-refresh to clear CSS cache).

**Files modified**
- `themes/karenderia_v2/views/store/index.php`, `themes/karenderia_v2/views/store/menu.php`,
  `themes/karenderia_v2/views/store/location_index.php`,
  `themes/karenderia_v2/views/store/feed-locations.php`,
  `themes/karenderia_v2/views/account/checkout.php`,
  `themes/karenderia_v2/assets/css/custom.css`, `themes/karenderia_v2/AGENTS.md`,
  `docs/custom.css.md`, `CLAUDE.md`.

**Known limitations / next steps**
- The hero's chef illustration and food-photography background from the mockups are not in the
  repo's assets; the hero approximates them with a gradient + isometric art. Add those images
  and wire them into `#main-search-banner` for a pixel match.
- Deep theming still excludes some inner pages (account, wallet, orders history).

### 2026-07-24 — New "everything app" home banner (neon, glassmorphic, FR/EN)

**Objectives**
- Replace the home hero with the full-width dark "everything app" banner from the new mockups
  (green/blue/orange/red neon service palette, glassmorphic, modern, mobile-responsive), with
  a French version under French locale and an English version under English. Presentational.

**What was changed**
- **Home banner** (`views/store/index.php` + `custom.css` §5): replaced the centred hero with a
  full-width, always-dark `.tf-banner` (glassmorphic, neon palette): the ThunderfamEats
  wordmark, a colour-accented headline (`.tf-banner-title`) + subtitle, the preserved
  `component-auto-complete` location search, a **10-card service category grid**
  (`.tf-cat-grid`/`.tf-cat` — Home Services, Barber, Hairdresser, Book Taxi, Hotel,
  Book Restaurants, Order Food, Grocery, Delivery, Takeout — each a neon-outlined glass card
  with an inline-SVG icon in green/blue/orange/red) and a **4-item trust bar** (`.tf-trust`:
  Trusted & Verified, Fast & Convenient, Quality Service, Available Near You).
- **FR/EN**: the view computes `$tfFr = stripos((string)Yii::app()->language,'fr')===0` and
  emits French copy when the site language starts with `fr`, English otherwise. This delivers
  both language versions out of the box without depending on translation-DB entries.
- **Responsive**: category grid 5 → 3 → 2 columns (≤991 / ≤575), trust bar 4 → 2 → 1; verified
  no horizontal overflow.
- Icons are inline SVGs (no icon-font dependency); the banner is always dark regardless of the
  site light/dark toggle, matching the mockups.

**Verification**
- Static preview (served via `python -m http.server`, removed before commit): computed styles
  confirmed gradient banner, 5-col grid, per-colour neon borders/icons (green #34d17a, blue
  #4aa3ff, orange #f0a53a, red #ff6065), 16px glass cards with backdrop-blur, 10 cards + 4 trust
  items, and responsive collapse with no overflow. WAMP was offline; live check + hard-refresh
  is on the owner's list. `component-auto-complete` left byte-identical.

**Files modified**
- `themes/karenderia_v2/views/store/index.php`,
  `themes/karenderia_v2/assets/css/custom.css`, `themes/karenderia_v2/AGENTS.md`,
  `docs/custom.css.md`, `CLAUDE.md`.

**Known limitations / next steps**
- Category cards are `href="javascript:;"` placeholders; wire each to its real service/listing
  route when those exist.
- The mockup's photo strip and phone-mockup render are AI composites not in the repo; add the
  image assets to `assets/images/` and place them in `.tf-banner` for a pixel match.
- The neon palette is applied to the banner; rolling it across all inner pages (menu/checkout/
  account) is a further step.

### 2026-07-25 — Nav wordmark logo + remove banner wordmark

**What was changed**
- Removed the `.tf-wordmark` text from the top of the home banner (`store/index.php`) and its
  orphaned CSS in `custom.css`; the banner now opens with the headline.
- Replaced the top-nav square site logo with the **ThunderfamEats brand image** the owner
  supplied at `/images/logo_thunderfameats.jpeg`: `components/views/site-logo.php` emits an
  `<img class="tf-nav-logo-img">` when `class_name` contains `top-logo` (footer/mobile
  placements keep the admin image). New CSS section 4b sizes it (40px tall, 32px on mobile)
  and rounds the corners so the on-black wordmark reads as a clean logo chip on either a light
  or dark nav.

**Why**
- The user asked to move the ThunderfamEats branding out of the banner and into the nav (in
  place of the square logo) and dropped the real logo file into `images/`.

**Files modified / added**
- Added: `images/logo_thunderfameats.jpeg` (brand logo asset).
- Modified: `themes/karenderia_v2/views/store/index.php`,
  `protected/components/views/site-logo.php`,
  `themes/karenderia_v2/assets/css/custom.css`, `themes/karenderia_v2/AGENTS.md`,
  `docs/custom.css.md`, `CLAUDE.md`.

**Not done here (needs decision) — category → merchant listing**
- The banner's 10 category cards remain `href="javascript:;"` placeholders. Making a click
  "list all merchants that have the service" requires the platform's real service-code
  taxonomy (the web storefront is food-delivery-focused and has no per-service listing yet,
  and the true services — Livraison/Pickup/Dinein/Book A Taxi/Book A Room/… — differ from the
  banner's illustrative labels). A safe implementation means adding a service filter to the
  merchant feed + a route and testing against the live DB, so it was deferred pending the
  owner's confirmation of the mapping/approach.

### 2026-07-25 — Logo shows only for guests + service-filtered category listing

**What was changed**
- **Nav logo (guests only):** `components/views/site-logo.php` now shows the ThunderfamEats
  brand image (`/images/logo_thunderfameats.jpeg`) in the top nav only when
  `Yii::app()->user->isGuest`; **logged-in** users keep the square admin logo icon.
- **Category → service-filtered feed** (the owner chose: link to the existing restaurants
  feed, filtered to the clicked service). Implemented by mirroring the existing
  `cuisine_filter` mechanism:
  - `views/store/index.php`: each banner category card links to
    `/store/restaurants?service=<code>` (codes: home_services, barber, hairdresser, book_taxi,
    hotel, book_restaurants, order_food, grocery, delivery, takeout).
  - `StoreController::actionRestaurants`: reads `?service=` and emits `var service_filter=...`.
  - `assets/js/front.js` (`#vue-feed`): adds a `service` field, reads `service_filter` on
    mount, and includes `'service'` in the `getFeedV1` filters.
  - `CMerchantListingV1::preFilter`: adds an **additive** `case "service"` that restricts
    merchants to those with a `{{services_fee}}` row joined to `{{services}}` on
    `service_code`. No-op when absent, so the default feed query is unchanged.

**Why**
- The owner asked to move branding into the nav (guests) while keeping the compact square icon
  for signed-in users, and to make category clicks list merchants offering that service.

**Verification / caveats**
- No local PHP/DB (WAMP offline) — the filter is additive and fails safe: without `?service=`
  the feed is byte-for-byte unchanged; with an unknown code it simply returns no merchants
  rather than erroring. The card `service` codes are label-derived and **must be aligned to the
  live `st_services.service_code` values** (the demo dump only has delivery/pickup/dinein/pos;
  production has more). Owner to verify on the live server and adjust codes as needed.
- The feed is location-gated: `/store/restaurants` redirects to `/` until a delivery location
  is set, so a category click filters once the location (search pill) is chosen.

**Files modified / added**
- Modified: `themes/karenderia_v2/views/store/index.php`,
  `protected/components/views/site-logo.php`,
  `protected/controllers/StoreController.php`,
  `protected/components/CMerchantListingV1.php`, `assets/js/front.js`,
  `themes/karenderia_v2/AGENTS.md`, `docs/custom.css.md`, `CLAUDE.md`.

### 2026-07-25 — Transparent nav logo + tinted category theme + Rubik font

**What was changed**
- **Nav logo → transparent PNG, resized:** `components/views/site-logo.php` now serves
  `/images/logo_thunderfameats.png` (transparent wordmark) for guests, and `custom.css` §4b
  sizes it small (30px tall, 26px on mobile, `object-fit: contain`, no rounded chip) so the
  full wordmark is visible without clipping. Added `images/logo_thunderfameats.png`.
- **Service category theme (from the app "Catégories" mockup):** `custom.css` category cards
  (`.tf-cat`) now have a **subtle per-colour tinted fill** (`--cbg`) and **centred icon +
  label**, and the 10 cards cycle the full **green/blue/orange/red** neon palette
  (`store/index.php` card classes reassigned). The card `service` links are unchanged.
- **Site font → Rubik:** loaded from Google Fonts in `views/layouts/main-layout.php` and
  applied site-wide to text elements via `custom.css` §2b (`--tf-font`). Rubik is the closest
  freely-licensed match to Uber's **proprietary "Uber Move"** typeface (which cannot legally be
  embedded). Icon fonts (zmdi / fontawesome / element-plus) are deliberately not targeted, so
  glyph icons keep rendering — verified via a static preview (body/headings/buttons resolve to
  Rubik; a `.zmdi` element keeps `Material-Design-Iconic-Font`).

**Why**
- The owner asked to use the transparent PNG logo sized to be fully visible, apply the attached
  neon category colour theme, and match the UberEats site font.

**Files modified / added**
- Added: `images/logo_thunderfameats.png`.
- Modified: `themes/karenderia_v2/views/layouts/main-layout.php`,
  `themes/karenderia_v2/views/store/index.php`,
  `protected/components/views/site-logo.php`,
  `themes/karenderia_v2/assets/css/custom.css`, `themes/karenderia_v2/AGENTS.md`,
  `docs/custom.css.md`, `CLAUDE.md`.

**Notes**
- Rubik loads from Google Fonts (consistent with the site's existing CDN use); self-host it if
  you prefer no external dependency. The exact Uber Move is proprietary and was not used.
