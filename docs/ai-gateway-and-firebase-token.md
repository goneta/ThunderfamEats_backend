# AI gateway + Firebase custom-token endpoints

Two customer-app endpoints added to `InterfaceController` (Yii 1.x front app).
Both are authenticated as a **customer** (`verifyCustomer` in
`InterfaceCommon::accessRules()`) and reachable under the existing CSRF-exempt
`interface/*` route group.

| Endpoint | Purpose |
|----------|---------|
| `POST /interface/firebaseCustomToken` | Mint a Firebase custom token so the app can sign in to Firestore and participant-only chat rules apply. |
| `POST /interface/aichat` | Server-side conversational AI assistant (read-only). |

Everything is **disabled until configured** — with no config, each endpoint
returns a non-`1` `code` and a message, and the app degrades gracefully
(chat shows a connect error; the assistant falls back to its deterministic
read-only intents).

---

## 1. `firebaseCustomToken`

Fulfils the contract in the app repo:
`MobileVue/docs/backend/firebase-custom-token.md`.

- **Auth:** `Authorization: token <client_token>` (standard customer auth). Body empty.
- **Behaviour:** resolves `Yii::app()->user->client_uuid`, signs an RS256 JWT
  (via the already-vendored `Firebase\JWT\JWT`) with the service-account key,
  `uid = client_uuid`, `aud = https://identitytoolkit.googleapis.com/google.identity.identitytoolkit.v1.IdentityToolkit`, 1-hour expiry.
- **Response:** `{ "code":1, "msg":"", "details":{ "token":"<jwt>" } }`.

### Config

In `k-config.php` (git-ignored):

```php
define( 'FIREBASE_SA_JSON_PATH', '/secure/path/thunderfameats-firebase-adminsdk.json' );
```

- Use the **same Firebase project** as the app's chat (the one whose
  `firebase_projectid` is in the back-office Options / used by `chat.js`).
- Download the service-account JSON from Firebase console → Project settings →
  Service accounts → *Generate new private key*.
- Store it **outside the web root** and outside git. `.gitignore` already blocks
  `*firebase-adminsdk*.json`; verify with `git check-ignore <path>` if you keep
  it in-repo (not recommended).
- The endpoint reads `client_email` + `private_key` from that JSON.

### Deploy Firestore rules

Once tokens are minted, deploy the app's `firestore.rules` (participant-only).
Before this endpoint existed, rules had to be permissive; now they can require
`request.auth.uid` ∈ `participants`.

### Test

```bash
curl -s -X POST 'https://thunderfameats.com/interface/firebaseCustomToken' \
  -H 'Authorization: token <CLIENT_TOKEN>'
# expect: {"code":1,"msg":"","details":{"token":"eyJhbGciOiJSUzI1Ni...."}}
```

Then in the app: open Chat — `ensureFirebaseAuth()` exchanges the token via
`signInWithCustomToken` and Firestore reads/writes carry `request.auth.uid`.

---

## 2. `aichat` (AI assistant gateway)

- **Auth:** `Authorization: token <client_token>`.
- **Body:** `{ "message": string, "history"?: [{ "role":"user"|"assistant", "content":string }], "context"?: string }`.
  - `context` is optional read-only text the app already holds (e.g. a recent-orders
    summary the signed-in customer is authorized to see). The gateway does **not**
    re-query order internals in v1 — it forwards this context to the model.
- **Behaviour:** builds a system prompt (app description + customer first name +
  current locale + the provided context), calls the configured LLM via cURL with
  the **server-side** key, returns the assistant text. It is **read-only**: the
  prompt forbids claiming to place/modify/cancel/pay; those stay on the app's own
  screens (and the future Phase 4b confirmation-card flow).
- **Response:** `{ "code":1, "msg":"", "details":{ "reply": string } }`.

### Config

In `k-config.php`:

```php
define( 'TFE_AI_PROVIDER', 'anthropic' );        // v1 supports 'anthropic'
define( 'TFE_AI_MODEL',    'claude-sonnet-5' );
define( 'TFE_AI_API_KEY',  'sk-ant-...' );        // server-side only
```

The key never leaves the server. To add another provider, extend
`InterfaceController::aiCall()`.

### Enabling the app's full mode

The app auto-upgrades from deterministic mode to the gateway when the backend
advertises it. Add to the customer `getAttributes` payload (Options →
front settings) a boolean the app reads as `ai_assistant_enabled` (see
`MobileVue/src/api/assistant.js` → `hasGateway()`), then set it true. Until then
the app keeps answering "where is my order?" / "my recent orders" with real
order cards over existing read-only endpoints.

### Test

```bash
curl -s -X POST 'https://thunderfameats.com/interface/aichat' \
  -H 'Authorization: token <CLIENT_TOKEN>' -H 'Content-Type: application/json' \
  -d '{"message":"Bonjour, comment suivre ma commande ?"}'
# expect: {"code":1,"msg":"","details":{"reply":"..."}}
```

---

## Files changed (this bundle)

- `protected/controllers/InterfaceController.php` — `actionfirebaseCustomToken`,
  `loadFirebaseServiceAccount`, `actionaichat`, `aiCall`.
- `protected/controllers/InterfaceCommon.php` — added `firebaseCustomToken` and
  `aichat` to the `verifyCustomer` access list.
- `k-config.sample.php` — documented the new defines.
- `docs/ai-gateway-and-firebase-token.md` — this file.

No route/CSRF change needed: `interface/*` is already in
`front_main.php` → `noCsrfValidationRoutes`.

## Verification checklist (owner)

- [ ] `php -l protected/controllers/InterfaceController.php` (no CLI was available
      where this was authored — lint on your host).
- [ ] Set config in `k-config.php`; confirm `git check-ignore k-config.php`.
- [ ] `curl` both endpoints with a real client token against a staging DB.
- [ ] Check `protected/runtime/application.log` for `application.interface.aichat`
      errors on provider failures.
- [ ] Deploy `firestore.rules` only after `firebaseCustomToken` returns tokens.
- [ ] Ensure `YII_DEBUG` is `false` in production `index.php`.

## Security notes

- Service-account key and AI key live only in server config, never in the app.
- The AI gateway is read-only by construction (prompt + no tool/DB writes).
- Both endpoints run under the existing customer auth; the AI can never exceed
  what the signed-in user could already do. Write/financial AI actions are out of
  scope here (Phase 4b: confirmation cards).
