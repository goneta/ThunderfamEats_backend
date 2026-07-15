# protected/controllers/InterfaceController.php

Reference doc for
[`/protected/controllers/InterfaceController.php`](../protected/controllers/InterfaceController.php).
Owning contract: [`protected/controllers/AGENTS.md`](../protected/controllers/AGENTS.md).

## File purpose

The primary customer / mobile-app JSON API. The single largest controller (~11k lines),
`InterfaceController extends InterfaceCommon`. Handles customer authentication, location and
geocoding, address book, cuisine/merchant discovery and the storefront feed consumed by the
customer app.

## Responsibilities (action groups)

- **Auth & account:** `registerUser`, `userLogin`, `SocialRegister`, `requestCode`,
  `verifyCodeSignup`, `getAccountStatus`.
- **Locations & geo:** `getLocationCountries`, `getlocationAutocomplete`,
  `getLocationDetails`, `reverseGeocoding`, `validateCoordinates`, `SavePlaceByID`.
- **Address book:** `getAddresses`, `saveClientAddress`, `deleteAddress`, `addressAtttibues`.
- **Discovery / feed:** `CuisineList`, `getFeaturedMerchant`, `getMerchantFeed`,
  `getMerchantFeed2`, `getMerchantFeedAuth`, `searchAttributes`, `getBanner`.

## Architecture overview

Extends `InterfaceCommon`, which provides shared bootstrap (auth resolution, response
helpers, DB setup). Actions parse the request, call components/models, and emit the standard
JSON envelope. Business logic should live in components (`CMerchantListingV1`, `CMaps`,
`CClientAddress`, `CCuisine`, identities) rather than inline.

## Entry points

Routed under the `interface/*` prefix (CSRF-exempt in `front_main.php`
`noCsrfValidationRoutes`). Called by the ThunderfamEats customer app.

## Public actions

The `action*` methods above are the HTTP endpoints. Base setup lives in `InterfaceCommon`.

## Dependencies

- Components: `CMaps`/`MapSdk`, `CMerchantListingV1`, `CMerchantMenu`, `CClientAddress`,
  `CCuisine`, `CSocialLogin`, `CRecaptcha`, `CSMSsender`, identity classes.
- Models: `AR_clientsignup`, `AR_customer_login`, `AR_client_address*`, `AR_map_places`,
  `AR_favorites`.

## Database interactions

Reads/writes customer, address, and catalog tables via `AR_*` models and component queries
(table prefix `st_`).

## API endpoints

`POST/GET /interface/<action>` returning `{ code, msg, details }`. `code === 1` = success.

## Inputs / Outputs

- **Input:** request params (phone/email, credentials, coordinates, place ids, filters) via
  the sanitizing `CmsInput`.
- **Output:** JSON envelope with `details` payload (tokens, address lists, merchant feed…).

## Side effects

Creates accounts, sends verification codes (SMS/email via jobs), persists addresses, and may
enqueue jobs.

## Security considerations

- CSRF-exempt and largely public: validate and rate-limit auth actions
  (`requestCode`/`verifyCodeSignup`) to resist enumeration/abuse; use `CRecaptcha` where wired.
- Return per-user tokens on login; never leak password hashes or other users' PII.
- Bind all SQL parameters; never concatenate request input.

## Error handling

Failures return the envelope with `code != 1` and a message in `msg`; unexpected errors hit
the Yii error handler and `application.log`.

## Performance considerations

Feed/discovery actions are hot paths — lean on the Redis cache and the 60s schema cache;
avoid N+1 queries in merchant listing.

## Known limitations

- Monolithic (~11k lines); action bodies carry logic that ideally lives in components.
- Typo in action name `addressAtttibues` is part of the public contract — renaming breaks the
  app.

## Future improvements

- Extract action logic into dedicated components/services; add automated endpoint tests.

## Related files

- Base: `protected/controllers/InterfaceCommon.php`
- Routing/CSRF: [`front_main.php.md`](front_main.php.md)
- Services: [`protected/components/AGENTS.md`](../protected/components/AGENTS.md)
- Data: [`protected/models/AGENTS.md`](../protected/models/AGENTS.md)
