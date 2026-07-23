# themes/karenderia_v2/assets/css/custom.css

Reference doc for
[`/themes/karenderia_v2/assets/css/custom.css`](../themes/karenderia_v2/assets/css/custom.css).
Owning contract: [`themes/karenderia_v2/AGENTS.md`](../themes/karenderia_v2/AGENTS.md).

## File purpose

The **2026 UI redesign layer**. A single stylesheet that restyles the public storefront home
page and the merchant-signup page to the new ThunderfamEats visual identity (dark-first, green
accent, card-based layout) with full **light/dark** theming — without editing the legacy
`style.css`.

## Why it exists / how it works

`custom.css` is enqueued **last** in the `front-css` bundle
(`AssetsFrontBundle::registerBundle`: `style.css` → `responsive.css` → `custom.css`), so its
rules win by cascade order. This keeps the large, fragile legacy `style.css` untouched and
makes the redesign additive and easy to revert (empty the file).

## Structure

1. **Design tokens** — `--tf-*` CSS custom properties on `:root` (green accent, backgrounds,
   text, borders, nav/footer, shadows, radii, three advantage-card accent colors).
2. **Theming** — `:root[data-theme="dark"]` overrides the tokens; a
   `@media (prefers-color-scheme: dark)` block applies the dark tokens when the user hasn't
   explicitly chosen (`:root:not([data-theme="light"])`).
3. **Global** — buttons (`.btn-green`, `.tf-btn*`), top nav, and the theme-toggle button.
4. **Home (`html.tf-home`)** — hero (`#main-search-banner` → `.tf-hero` two-column), section
   titles, advantage cards (`.tf-advantages`/`.tf-adv-card`), promo cards
   (`.section-benefits .benefits` with illustration backgrounds), join banner, app section.
5. **Merchant (`html.tf-merchant`)** — hero (`.tf-merchant-hero`), the signup form card
   (`#vue-merchant-signup`), a new "How it works" section (`.tf-how`/`.tf-steps`), and the
   restyled "Why partner" grid (`.partner-section`).
6. **Restaurants / feed (`html.tf-feed`, `/store/restaurants` → `feed.php`)** — page
   background, the filter sidebar as a card (`.section-filter`), restaurant result cards
   (a `.tf-store-card` wrapper added inside each `.list-items` column), the "Show more"
   button, and the fast-delivery banner.
7. **Footer** — global `.sub-footer` / `footer` theming.
8. **Responsive** — breakpoints at 991px / 767px / 575px collapse grids and stack the hero.

## Inputs / Outputs

- **Input:** the `data-theme` attribute on `<html>` and the `.tf-home` / `.tf-merchant`
  page-scope classes (both set by small inline scripts in the views/layout).
- **Output:** the rendered appearance. No behavior.

## Dependencies

- Consumes markup/classes added in `views/layouts/main-layout.php` (no-FOUC script + toggle
  handler), `views/layouts/top-nav.php` (`#tf-theme-toggle`), `views/store/index.php`
  (hero + `.tf-advantages`), `views/merchant/merchant-signup.php` (hero + `.tf-how`).
- References existing theme images (`full-header@2x.png`, `addons-*_new.png`, `benefits-*.png`,
  `register.png`) via `../images/...`.
- Icons for the toggle/steps use the already-bundled material-design-iconic-font (`zmdi`).

## Side effects

None (pure CSS). The related toggle script writes the chosen theme to `localStorage`
(`tf-theme`).

## Security considerations

None — presentational only. No data, no network, no secrets.

## Performance considerations

Small single file; loads with the existing bundle (cache-busted by `?time=`). Uses CSS
variables and transforms; no runtime cost of note.

## Known limitations

- Deep theming (page background/cards) is scoped to the redesigned pages (home, merchant
  signup, restaurants/feed); other inner pages (menu, cart, checkout) keep the legacy look but
  inherit the themed nav/footer, so a toggled dark mode is only fully realized on those pages.
- The location-mode restaurants view (`feed-locations.php`, used only when
  `home_search_mode = "location"`) is not yet covered; this site runs in address mode.
- The merchant form keeps its real functional fields (store name, address autocomplete,
  membership, services, recaptcha); it is not reduced to the simplified fields shown in the
  mockup, to preserve the registration flow.

## Future improvements

- Extend token-based theming to inner pages (restaurants, menu, checkout).
- Promote the `--tf-*` tokens into `style.css` and retire duplicated legacy colors.

## Related files

- [`themes/karenderia_v2/AGENTS.md`](../themes/karenderia_v2/AGENTS.md)
- Views: `views/layouts/main-layout.php`, `views/layouts/top-nav.php`,
  `views/store/index.php`, `views/merchant/merchant-signup.php`
- Enqueue point: `protected/components/AssetsFrontBundle.php`
