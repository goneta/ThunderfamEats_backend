# themes/karenderia_v2 — Front-end Theme (View Layer)

## Purpose

The active front-end theme: all storefront/customer-web views and their CSS/JS/image assets.
Rendered by the `protected/` application (`'theme' => 'karenderia_v2'` in
`config/front_main.php`). Controllers call `$this->render('<view>')`, which resolves to
`views/<controller-or-shared>/<view>.php` here.

## Ownership

Governs everything under `themes/karenderia_v2/`. Inherits the repo-wide contract from the
root `AGENTS.md`. Presentation only — no business logic, routes, or data access live here.

## Structure

- `views/layouts/` — page shells. `main-layout.php` is the HTML skeleton (`<head>`/`<body>`);
  `main_layout.php` composes nav + content + sub-footer + footer. Also `top-nav.php`,
  `footer.php`, `sub-footer.php`.
- `views/store/` — storefront pages. `index.php` is the home page (rendered by
  `StoreController::actionIndex`). `join-us.php` is the merchant CTA banner partial.
- `views/merchant/` — merchant flow. `merchant-signup.php` is the partner registration page.
- `views/account/`, `views/orders/`, `views/menu*`, `views/cart*`, etc. — feature views.
- `assets/css/` — `style.css` (main), `responsive.css`, and **`custom.css`** (the redesign
  layer, loaded LAST — see below). `style1/_new/_old.css` are historical variants.
- `assets/images/`, `assets/icons/`, `assets/flag/` — theme media.

CSS/JS are enqueued by `protected/components/AssetsFrontBundle.php` (the `front-css` bundle:
`style.css` → `responsive.css` → `custom.css`), not linked directly in the layout.

## Local Contracts

- **Redesign layer:** the 2026 UI redesign lives in `assets/css/custom.css` (design tokens,
  light/dark theming, hero/cards/footer/signup styling). It loads last so it overrides
  `style.css` without editing it. Prefer adding presentational rules there over touching
  `style.css`. See [`docs/custom.css.md`](../../docs/custom.css.md).
- **Light/dark theme:** driven by `data-theme` on `<html>`. A no-FOUC script in
  `views/layouts/main-layout.php` applies the saved theme before paint; a toggle button in
  `views/layouts/top-nav.php` (`#tf-theme-toggle`) flips it and persists to `localStorage`
  (`tf-theme`); a click handler sits at the end of `main-layout.php`. Absent a saved choice,
  `prefers-color-scheme` decides.
- **Page scoping:** each redesigned view adds a class to `<html>` via a one-line inline script
  so page-specific restyles in `custom.css` don't leak to other pages — `tf-home`
  (`store/index.php`), `tf-merchant` (`merchant/merchant-signup.php`), `tf-feed`
  (`store/feed.php`, `/store/restaurants`), `tf-menu` (`store/menu.php`), `tf-checkout`
  (`account/checkout.php`), and `tf-locations` (`store/location_index.php`,
  `store/feed-locations.php`). Nav/footer theming is intentionally global.
- **Home hero:** `store/index.php`'s hero is a centred, full-bleed band
  (`.tf-hero.tf-hero-centered`) — a themed gradient with the isometric illustration bleeding
  right and a translucent "Locate Your Location" pill + subtitle. The advantage cards are
  filled red/yellow/blue (the `addons-*_new.png` art is drawn for those colours). The mockups'
  chef + food-photo assets are not in the repo; add them to `assets/images/` to match pixel-for-pixel.
- **feed.php note:** the real results route is `StoreController::actionRestaurants`, which
  passes `tabs_suggestion` / `responsive` / `place_details`. The bare `actionFeed`
  (`/store/feed`) renders the same view without those vars and 500s under `YII_DEBUG` — a
  pre-existing quirk, not a redesign regression. Verify feed changes via `/store/restaurants`.
- **Do not alter functional markup** when restyling: Vue component tags and directives
  (`component-auto-complete`, `v-model`, `@submit`), form field `id`/`name`, `t()` source
  strings, and route URLs (`Yii::app()->createUrl(...)`) must stay byte-identical — only
  wrappers, classes, and copy-for-display change.
- User-facing strings go through `t()`; new strings need matching entries in the translation
  DB to render in non-English locales.

## Common workflows

- **Restyle a section:** add/adjust rules in `custom.css` using the `--tf-*` design tokens;
  scope page-specific rules under `html.tf-home` / `html.tf-merchant`.
- **Add a themed color:** define it as a `--tf-*` variable in both the light `:root` block and
  the `[data-theme="dark"]` + `prefers-color-scheme` blocks.

## Verification

No automated tests. Verify visually by loading the home and merchant-signup pages in light and
dark mode across desktop/tablet/mobile widths, and confirm the search box, merchant form,
recaptcha and submit still function. (A throwaway static preview served over `http.server`
was used to confirm the redesign computes correctly in both themes.)

## Security considerations

- Theme JS is UI-only; never place tokens, secrets or PII in view scripts.
- Escape dynamic values in views with `CHtml::encode()` / `CJavaScript::quote()` as the
  existing views do.

## Related documentation

- Redesign stylesheet: [`docs/custom.css.md`](../../docs/custom.css.md)
- Asset bundling: `protected/components/AssetsFrontBundle.php`
  (see [`protected/components/AGENTS.md`](../../protected/components/AGENTS.md))
- Controllers that render these views:
  [`protected/controllers/AGENTS.md`](../../protected/controllers/AGENTS.md)
- Reference index: [`/docs`](../../docs/README.md)
