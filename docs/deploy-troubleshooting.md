# Deployment troubleshooting — customer app cannot reach the backend

Symptoms seen on the dev/test VPS (`test.thunderfameats.com`), 2026-07-25:

- The mobile app is stuck on the "Enter your location" screen: **no map renders**,
  only skeleton placeholders and the Confirm button.
- The backoffice returns **HTTP 500**.

Both had backend causes. The app was fine.

---

## Diagnosis chain

The location screen only renders the map when `DataStore.hasMapConfig` is true.
`maps_config` comes from the backend, so if `/interface/*` returns nothing the
map never appears and the screen looks frozen. Working backwards:

```
backoffice runtime dir missing/not writable  -> 500, cannot open API Access page
        -> no app token can be generated for this host
        -> /interface/* rejects the app token -> 302 to /account/login (empty body)
        -> app receives no maps_config -> map never renders -> "stuck" location screen
```

### 1. `/interface/*` answers `302 -> /account/login` instead of JSON

Reproduce:

```bash
curl -s -o /dev/null -D - -X POST 'https://<host>/interface/getAttributes' \
  -H 'Authorization: Bearer <APP_TOKEN>' -H 'Content-Type: application/json' -d '{}'
# HTTP/1.1 302 Found ... Location: https://<host>/account/login
```

A 302 here means `AppIdentity::getIdentity()` returned false, so the access rule
denied the request and Yii redirected to the login page. The app then receives an
empty body. `getIdentity()` requires **all three** of:

1. the token exists in the DB — `option` row with `merchant_id = 0` and
   `option_name = 'website_jwt_token'` (see `AttributesTools::JwtMainTokenID()`);
2. the token verifies against **this server's** `CRON_KEY` (HS256);
3. **the token's `iss` claim equals this server's hostname**
   (`$owner === Yii::app()->request->getServerName()`).

Condition 3 is the one that bites when reusing a token across environments: a
token minted on `thunderfameats.com` will always be rejected by
`test.thunderfameats.com`. Decode the payload to check:

```bash
echo '<APP_TOKEN>' | cut -d. -f2 | tr '_-' '/+' | base64 -d
# {"iss":"thunderfameats.com", ...}   <- must match the host that serves it
```

**Fix:** generate a token *on that host* (backoffice -> **API Access**, which does
`iss = Yii::app()->request->getServerName()`, signs with that server's `CRON_KEY`
and stores it in the `option` table), then put it in the app's `MobileVue/.env`
as `API_TOKEN` and rebuild. Do **not** relax the `iss` check — it is what binds a
token to a deployment.

### 2. Backoffice returns HTTP 500

Body contains:

> Application runtime path "..." is not valid. Please make sure it is a
> directory writable by the Web server process.

Yii needs `protected/runtime/` (app) and `backoffice/protected/runtime/`
(backoffice) to exist **and** be writable by the web server user.

Two independent requirements:

- **Existence.** Git cannot store empty directories. `.gitignore` ignores
  `protected/runtime/*` and `backoffice/protected/runtime/*` while negating
  `.gitkeep`, but the `.gitkeep` files were missing, so a fresh clone/deploy had
  no `runtime` directories at all. Fixed in this repo by committing both
  `.gitkeep` files.
- **Permissions.** Git creates them owned by the deploying user, not the web
  server. On the VPS:

```bash
chown -R www-data:www-data protected/runtime backoffice/protected/runtime assets backoffice/assets
chmod -R 775 protected/runtime backoffice/protected/runtime assets backoffice/assets
```

(`www-data` for Debian/Ubuntu Apache; adjust for your setup. `assets/` is
included because Yii publishes its asset bundles there and fails the same way.)

---

## Post-deploy checklist for a new environment

- [ ] `protected/runtime`, `backoffice/protected/runtime`, `assets`,
      `backoffice/assets` exist and are writable by the web server user.
- [ ] `k-config.php` present with that environment's DB credentials and `CRON_KEY`.
- [ ] Backoffice loads (no 500), admin can log in.
- [ ] Backoffice -> **API Access** -> generate the app token **on this host**.
- [ ] App `MobileVue/.env`: `API_BASE_URL=https://<host>` (no trailing slash, no
      `/interface`) and `API_TOKEN=<the token just generated>`; rebuild the app.
- [ ] `curl` `/interface/getAttributes` returns `{"code":1,...}` — not a 302.
- [ ] Maps are configured (provider + key + `default_lat`/`default_lng`) so the
      app's location screen can render.
- [ ] `YII_DEBUG` is `false` in `index.php` and `backoffice/index.php` before the
      host is publicly reachable — it currently defaults to `true`, which serves
      full stack traces (file paths and source) to anonymous visitors.
- [ ] For chat + AI: see `docs/ai-gateway-and-firebase-token.md`
      (`FIREBASE_SA_JSON_PATH`, `TFE_AI_API_KEY`).
