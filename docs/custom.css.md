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
4. **Home (`html.tf-home`)** — a full-width, always-dark **"everything app" banner**
   (`#main-search-banner` → `.tf-banner`, glassmorphic, neon service palette): the ThunderfamEats
   wordmark, a headline (`.tf-banner-title`) + subtitle, the preserved "Locate Your Location"
   search (`.home-search-wrap`), a **service category grid** (`.tf-cat-grid`/`.tf-cat` — 10
   neon-outlined cards in green/blue/orange/red with inline-SVG icons) and a **trust bar**
   (`.tf-trust`/`.tf-trust-item` — 4 items). FR/EN copy is chosen in the view from
   `Yii::app()->language`. Then the "Business type" heading, **filled colour** advantage cards
   (`.tf-advantages`/`.tf-adv-card`), promo cards (`.section-benefits .benefits`), join banner
   and app section.
5. **Merchant (`html.tf-merchant`)** — hero (`.tf-merchant-hero`), the signup form card
   (`#vue-merchant-signup`), a new "How it works" section (`.tf-how`/`.tf-steps`), and the
   restyled "Why partner" grid (`.partner-section`).
6. **Restaurants / feed (`html.tf-feed`, `/store/restaurants` → `feed.php`)** — page
   background, the filter sidebar as a card (`.section-filter`), restaurant result cards
   (a `.tf-store-card` wrapper added inside each `.list-items` column), the "Show more"
   button, and the fast-delivery banner.
7. **Menu / cart / checkout / location (`html.tf-menu`, `html.tf-checkout`,
   `html.tf-locations`)** — page background, headings/text, Bootstrap `.card` + element-plus
   `.el-card` panels, the menu category sidebar (`.sticky-sidebar` + `.menu-category`), form
   controls, and the location-mode sidebar/results. Theming only, no markup changes.
8. **Footer** — global `.sub-footer` / `footer` theming.
9. **Responsive** — breakpoints at 991px / 575px collapse grids; the banner category grid goes
   5 → 3 → 2 columns and the trust bar 4 → 2 → 1, with no horizontal overflow.

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

- Deep theming now covers home, merchant signup, restaurants/feed, menu, cart, checkout and
  the location-mode results; remaining deep inner pages (account, wallet, orders) keep the
  legacy look but inherit the themed nav/footer.
- The home hero's chef illustration and food-photography background shown in the source
  mockups are **not** in the theme's asset set; the hero approximates them with a themed
  gradient plus the existing isometric illustration. Drop those images into
  `assets/images/` and wire them into `#main-search-banner` for a pixel match.
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
