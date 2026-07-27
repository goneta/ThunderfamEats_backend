# CinetPay — "Mobile Money" payment gateway

CinetPay aggregates Orange Money, MTN Mobile Money, Moov Money and Wave behind
one hosted checkout. The customer sees a single **Mobile Money** method at
checkout; CinetPay's page then shows the operators actually available for the
transaction. CinetPay is the technical provider — it is never the
customer-facing label.

Module: `protected/modules/cinetpay/` (follows the `touchpay`/`vivawallet`/
`razorpay` module conventions — see `protected/modules/AGENTS.md`).

## How the flow works

```
App checkout                     Backend                              CinetPay
────────────                     ───────                              ────────
select "Mobile Money"
Place Order ────────────────────► PlaceOrder: order saved UNPAID,
                                  returns payment_url =
                                  {site}/cinetpay/api/createcheckout?…
open payment_url ───────────────► createcheckout: fresh unique
                                  reference TFE{order_id}T{ts},
                                  meta rows saved, POST /v2/payment ──► hosted page
                                                                       (operator choice,
payer pays on their phone ◄────────────────────────────────────────── OTP/validation)
                                  notify webhook ◄─────────────────── POST notify_url
                                  HMAC x-token check (403 on forge)
                                  RE-VERIFY /v2/payment/check
                                  (unreachable → 503, CinetPay retries)
                                  ACCEPTED → order paid (idempotent)
payer's browser returns ────────► verifypayment: RE-VERIFY again
                                  (redirect proves nothing)
                                  paid → deep link / web redirect
app shows Track Order ◄───────── com.thunderfameats://payment-callback
                                  ?status=successful&order_id={uuid}
```

Security doctrine implemented (non-negotiable):

- **Verify-first**: neither the webhook body nor the browser return is ever
  trusted; every applied status comes from `/v2/payment/check`.
- **Idempotent**: the paid transition is guarded on `payment_status` and the
  transaction row is keyed by the unique reference — duplicate notifies and
  the notify/return race are safe no-ops. A confirmed payment is never
  downgraded.
- **Fail toward retry**: check API unreachable ⇒ webhook answers **503** and
  CinetPay re-delivers later. Nothing is guessed.
- **Credentials stay server-side**: `fetchpaymentmethod` sanitizes the
  cinetpay entry (`sanitizeServerOnlyCredentials`) and
  `getPaymentCredentialsPublic` exposes an empty key for it. The app performs
  zero CinetPay API calls.
- **Amount cross-check**: before applying success, the verified amount must
  equal the amount initiated (mismatch → refused + logged).
- Failed/cancelled/pending payments leave the order in its draft/unpaid state:
  no duplicate order, no stock movement, retry allowed (fresh reference per
  attempt — CinetPay transaction ids are single-use).

## Deployment (VPS)

1. `git pull` (module + config are in the repo).
2. Apply the SQL once (adjust `st_` to your DB_PREFIX):
   `protected/modules/cinetpay/filesupdate/v100.sql`
   — inserts the addon row and the `Mobile Money` gateway row, **inactive** and
   with empty credentials.
3. Back-office → **Payment Gateway** → *Mobile Money* → update:
   - **API Key** (attr1) and **Site ID** (attr2) — CinetPay panel → Integration.
   - **Secret Key** (attr3) — same page; enables HMAC validation of the notify
     `x-token`. Strongly recommended.
   - **Channels** (attr5) — `MOBILE_MONEY` (default) or `ALL` (adds cards).
   - **Currency override** (attr6) — optional, e.g. `XOF` if the shop currency
     label isn't a CinetPay code. `FCFA`/`CFA` are auto-mapped to `XOF`.
   - Status → **Active**.
4. Enable the gateway for each merchant that accepts it (merchant settings →
   payment gateways) — the checkout lists a gateway only when the merchant has
   it enabled (`merchant_meta.payment_gateway`), same as Stripe/Cash.
5. CinetPay panel: register the notify URL
   `https://<host>/cinetpay/api/notify` (it is also sent per-initiation).
6. **Sandbox vs production**: CinetPay has no separate sandbox host — test
   API keys on the same endpoints ARE the sandbox. Swapping to live panel keys
   is the only environment switch; the row's `is_live` flag is informational.

## Test matrix (staging, before go-live)

```bash
# 1. gateway appears at checkout (needs an active row + merchant enablement)
curl -s 'https://<host>/interface/fetchpaymentmethod?merchant_id=<id>' \
  -H 'Authorization: token <CLIENT_TOKEN>' | grep -o '"payment_code":"cinetpay"[^}]*'
#    → must NOT contain attr1/attr3 (credentials are sanitized)

# 2. place an order from the app with Mobile Money → browser opens the CinetPay
#    page → pay with a sandbox operator → app deep-links back to Track Order.

# 3. webhook forge → 403 (with Secret Key configured)
curl -s -o /dev/null -w "%{http_code}\n" -X POST 'https://<host>/cinetpay/api/notify' \
  -H 'x-token: forged' -d 'cpm_trans_id=TFE1T1'
#    → 403 (404 if the reference is unknown — send one from a real attempt)

# 4. duplicate notify → replayed delivery answers 200 with "applied":false
#    and the order/transaction rows are unchanged (idempotent no-op).

# 5. check protected/runtime logs, category application.cinetpay:
#    initiation, confirmation, forged-token and 503 events are all logged
#    (references and statuses only — never credentials).
```

## Unit tests

`php protected/modules/cinetpay/tests/run-tests.php` (WAMP:
`C:\wamp64\bin\php\php8.2.29\php.exe`) — 28 assertions covering the HMAC
vectors (accept/forge/tamper/absent-field), the verify-first status mapping
(ACCEPTED/REFUSED/CANCELLED/WAITING/PENDING/662→pending/600→unknown), amount
normalization (XOF multiples of 5, minimum 5) and currency mapping. The
controller flows (redirects, idempotent apply, 403/503 webhook paths) require
the Yii runtime + DB and are covered by the staging matrix above.

## Files

- `protected/modules/cinetpay/CinetpayModule.php` — module bootstrap
  (paymentInstructions online; refunds are back-office-only by design).
- `protected/modules/cinetpay/components/PaymentCinetpay.php` — thin REST
  client (initiate, check, HMAC, amount/currency normalization). No SDK: the
  official PHP package is a 2020 PHP-5.6-era release.
- `protected/modules/cinetpay/controllers/ApiController.php` — createcheckout,
  verifypayment (+ self-refreshing pending page), notify webhook,
  successful/failed/cancel pages.
- `protected/modules/cinetpay/filesupdate/v100.sql` + `front_main.txt`.
- `protected/config/front_main.php` — module registration + CSRF exemption.
- `protected/controllers/InterfaceController.php` — credential sanitizer.
- `protected/components/CPayments.php` — public-credentials case for cinetpay.
