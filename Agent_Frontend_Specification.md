# Agent - Frontend Specification Document

**Version:** 1.0 | **Source:** Lipa backend codebase analysis | **Date:** 2026-05-12  
**Status:** Single source of truth. Do not call or display anything that is not listed here.

---

## Table of Contents

1. [Scope](#1-scope)
2. [HTTP Contract](#2-http-contract)
3. [Agent Authentication](#3-agent-authentication)
4. [Agent Capability Map](#4-agent-capability-map)
5. [Exhaustive API Mapping](#5-exhaustive-api-mapping)
6. [Request Schemas](#6-request-schemas)
7. [Response Schemas](#7-response-schemas)
8. [Approval Payload Schemas](#8-approval-payload-schemas)
9. [Enums](#9-enums)
10. [Permissions](#10-permissions)
11. [Operational Rules](#11-operational-rules)
12. [Evidence Index](#12-evidence-index)

---

## 1. Scope

This document describes only the APIs an Agent frontend can interact with:

- `POST /api/v1/auth/agent/*`
- every endpoint under `/api/v1/agent/*`

Backoffice, customer self-service, merchant portal, terminal, service-provider callback, and server-to-server APIs are outside this Agent scope, except where an Agent endpoint explicitly returns customer, merchant, card, wallet, transaction, commission, or approval identifiers.

Total Agent endpoints in scope: **30**.

---

## 2. HTTP Contract

### 2.1 Base Rules

| Rule | Value |
|---|---|
| Body format | JSON unless explicitly stated otherwise |
| Multipart format | Used only by KYC document upload |
| Currency | KMF |
| Date-time format | ISO-8601 string |
| Date format | ISO local date, `YYYY-MM-DD` |
| Auth header | `Authorization: Bearer <accessToken>` for every protected endpoint |
| Optional tracing header | `X-Correlation-Id: <uuid>`; some Agent write endpoints generate one if absent |
| Required idempotency header | `Idempotency-Key: <opaque-key>` for Agent financial mutation endpoints listed in [11.2](#112-idempotency) |
| Rate limit | Every `/api/v1/auth/**` request is limited to 10 requests/minute/IP by default |

### 2.2 Success Envelopes

Most non-paginated success responses use:

```json
{
  "data": {},
  "timestamp": "2026-05-12T12:00:00Z"
}
```

Cursor-paginated or list-style endpoints using `PagedResponse` return:

```json
{
  "data": [],
  "pagination": {
    "nextCursor": "opaque-string-or-null",
    "hasMore": false,
    "limit": 20
  }
}
```

Cursor rules:

- `limit` defaults to `20`.
- Backend clamps `limit` to `1..100`.
- `cursor` is opaque; use `pagination.nextCursor` as-is.
- `GET /api/v1/agent/card-stock` uses `PagedResponse.last(...)`: `hasMore=false`, `nextCursor=null`, and `limit` equals the returned list size.

### 2.3 Error Envelopes

Controller and validation errors generally return:

```json
{
  "error": {
    "code": "VALIDATION_FIELD_REQUIRED",
    "message": "Request validation failed",
    "details": ["field: message"],
    "correlationId": "uuid",
    "timestamp": "2026-05-12T12:00:00Z"
  }
}
```

Security filter exceptions are special:

- `401` and `403` emitted by Spring Security return raw `ApiError`, without the outer `{ "error": ... }` wrapper.
- `401` emitted by `JwtAuthenticationFilter` for invalid, expired, or revoked bearer tokens also returns raw `ApiError`.
- `429` from `RateLimitingFilter` returns the wrapped error envelope.

### 2.4 CSV Responses

No Agent endpoint in this specification returns raw CSV.

---

## 3. Agent Authentication

### 3.1 Login

`POST /api/v1/auth/agent/login`

Request:

```json
{
  "phoneCountryCode": "269",
  "phoneNumber": "3212345",
  "pin": "1234"
}
```

Response `200 ApiResponse<LoginResponse>` when MFA is not required:

```json
{
  "data": {
    "mfaRequired": false,
    "tokens": {
      "accessToken": "jwt",
      "accessTokenExpiresAt": "2026-05-12T20:00:00Z",
      "refreshToken": "opaque-refresh-token",
      "refreshTokenExpiresAt": "2026-06-11T12:00:00Z"
    }
  },
  "timestamp": "2026-05-12T12:00:00Z"
}
```

Response `200 ApiResponse<LoginResponse>` when TOTP MFA is required:

```json
{
  "data": {
    "mfaRequired": false,
    "pinSetupRequired": false,
    "challengeId": "uuid",
    "mfaFactor": "TOTP"
  },
  "timestamp": "2026-05-12T12:00:00Z"
}
```

Response `200 ApiResponse<LoginResponse>` when the Agent has no PIN yet (created by Backoffice without an initial PIN, or after a Backoffice forced reset):

```json
{
  "data": {
    "mfaRequired": false,
    "pinSetupRequired": true,
    "pinSetupToken": "jwt-purpose-PIN_SETUP",
    "pinSetupTokenExpiresAt": "2026-05-12T12:10:00Z"
  },
  "timestamp": "2026-05-12T12:00:00Z"
}
```

The client must store the `pinSetupToken` (10 min TTL, single-use) and immediately call `POST /api/v1/auth/agent/auth-pin/setup` with it to define the initial PIN. No full session is issued on this branch; the submitted `pin` field on the login request is ignored when the actor has no configured PIN.

Token lifetimes from `application.yml`:

| Actor | Access TTL | Refresh TTL |
|---|---:|---:|
| Agent | 8h | 30d |

Login lockout:

- 3 failed auth-PIN checks lock the auth PIN for 15 minutes.
- `PENDING_KYC`, `SUSPENDED`, and `CLOSED` agents cannot obtain tokens.
- An Agent without an auth PIN never receives a full session: the login response is `pinSetupRequired=true` and carries a short-lived `pinSetupToken`.

### 3.2 MFA Verification

`POST /api/v1/auth/agent/login/verify-mfa`

Request:

```json
{
  "challengeId": "uuid",
  "code": "123456"
}
```

Response `200 ApiResponse<LoginResponse>`:

```json
{
  "data": {
    "mfaRequired": false,
    "tokens": {
      "accessToken": "jwt",
      "accessTokenExpiresAt": "2026-05-12T20:00:00Z",
      "refreshToken": "opaque-refresh-token",
      "refreshTokenExpiresAt": "2026-06-11T12:00:00Z"
    }
  },
  "timestamp": "2026-05-12T12:00:00Z"
}
```

MFA challenge rules:

- Challenge TTL is 5 minutes.
- Only `TOTP` challenges are produced by PIN-first Agent login.
- Invalid, expired, consumed, or wrong-actor challenges return `MFA_INVALID`.

### 3.3 Refresh

`POST /api/v1/auth/agent/refresh`

Request:

```json
{ "refreshToken": "opaque-refresh-token" }
```

Response: `200 ApiResponse<TokenResponse>`. The previous refresh token is marked replaced.

Refresh reuse protection:

- Reusing a replaced refresh token revokes all active refresh tokens for the Agent.
- Refresh tokens belonging to another actor type are rejected.
- `PENDING_KYC`, `SUSPENDED`, and `CLOSED` agents cannot refresh.

### 3.4 Logout

`POST /api/v1/auth/agent/logout`

Headers: `Authorization: Bearer <accessToken>`

Response: `204 No Content`.

Logout revokes:

- the current access token until its natural expiry window;
- all active refresh tokens for the authenticated Agent.

### 3.5 JWT Claims Used By Agent

The access token contains:

| Claim | Meaning |
|---|---|
| `sub` | Agent UUID |
| `jti` | Access token UUID |
| `act` | `AGENT` |
| `iat`, `exp` | JWT issued-at and expiry |

Agent JWTs do not carry `perms`, `brole`, `mid`, or `tid` claims.

### 3.6 Auth PIN Management

Two distinct endpoints, distinguished by the bearer they require:

- `POST /api/v1/auth/agent/auth-pin/setup` — accepts the short-lived `pinSetupToken` returned by `POST /login` when `pinSetupRequired=true`. Public route in the security config; the controller still verifies the bearer purpose (`PIN_SETUP`). The token is revoked on success so it cannot be reused.
- `PUT /api/v1/auth/agent/auth-pin` — requires an existing full-session Agent JWT. Used by an already-logged-in Agent to rotate their PIN; rejected with `AUTH_PIN_INVALID` (and a failed-attempt increment) when `currentPin` is wrong.

PIN rules:

- PIN format is 4 to 8 digits.
- Agents are created by Backoffice with no PIN. The first valid call is `/auth-pin/setup` after a `pinSetupRequired` login.
- The setup endpoint is rejected with `AUTH_PIN_ALREADY_SET` if a PIN already exists — in that case the Agent must use the change-PIN flow.
- Change requires `currentPin`; wrong current PIN counts toward the 3-failure, 15-minute lockout policy.
- PIN hashes are stored server-side with BCrypt (cost 12). The clear PIN is never logged or transmitted back.
- Forgotten-PIN self-service is not implemented. An Agent who has lost their PIN must contact Backoffice for a forced reset (`POST /api/v1/backoffice/agents/{id}/auth-pin/reset`), which clears the PIN state and returns the Agent to the `pinSetupRequired` branch at next login.

### 3.7 TOTP Enrollment

Agent TOTP endpoints require an existing Agent JWT:

- `POST /api/v1/auth/agent/totp-setup`
- `POST /api/v1/auth/agent/totp-confirm`
- `DELETE /api/v1/auth/agent/totp-setup`

Enrollment rules:

- Setup returns a fresh Base32 secret and an `otpauth://` URI.
- Confirm validates a 6-digit TOTP code against the pending secret, then activates enrollment.
- Revoke requires a current 6-digit TOTP code and clears the active TOTP secret.

### 3.8 Legacy OTP

Legacy OTP endpoints are still exposed:

- `POST /api/v1/auth/agent/otp-request`
- `POST /api/v1/auth/agent/otp-verify`

Production behavior from `application.yml`:

- `sms-otp.enabled=false` by default.
- When disabled, both legacy endpoints return `410 Gone` with `LEGACY_OTP_LOGIN_REMOVED`.

Dev behavior:

- `application-dev.yml` enables the legacy SMS OTP path.
- For TOTP-enrolled agents, legacy `otp-request` issues a TOTP challenge rather than sending SMS.
- For agents without TOTP, legacy `otp-request` issues an SMS OTP challenge.

---

## 4. Agent Capability Map

| Area | Agent can do |
|---|---|
| Session | PIN-first login, MFA verify, refresh token, logout |
| Auth PIN | set initial auth PIN, change auth PIN |
| TOTP | set up, confirm, revoke TOTP enrollment |
| Legacy OTP | request and verify legacy OTP only when backend config enables it |
| Profile | view own profile, balance, daily summary, assigned limits |
| Cash-in | load funds from Agent wallet to a customer wallet |
| Cash-out | withdraw merchant funds through the Agent; large cash-out can create a pending approval |
| Customer onboarding | enroll a customer and upload/list KYC documents for that customer |
| Customer lookup | look up a customer by phone for pre-transaction confirmation |
| Merchant lookup | look up a merchant by phone for pre-cash-out confirmation |
| Card lookup | look up a card by scanned NFC UID before report-lost / report-stolen / replace |
| Cards | list assigned card stock, sell a card, report a customer card lost/stolen, replace a customer card |
| Commissions | list own commission payout history |
| History | list own wallet-scoped transactions, view own transaction detail, list own ledger statement |

---

## 5. Exhaustive API Mapping

### 5.1 Auth

| Method | Path | Auth | Headers | Query | Request | Response | Primary errors | Frontend notes |
|---|---|---|---|---|---|---|---|---|
| POST | `/api/v1/auth/agent/login` | Public | none | none | `LoginRequest` | `200 ApiResponse<LoginResponse>` | `400 VALIDATION_FIELD_REQUIRED`, `401 INVALID_CREDENTIALS`, `422 AUTH_PIN_LOCKED`, `422 ACTOR_PENDING_KYC`, `422 ACTOR_SUSPENDED`, `422 ACTOR_CLOSED`, `429 TERMINAL_RATE_LIMIT` | If `pinSetupRequired=true`, call `/auth-pin/setup` with `pinSetupToken`. Else if `mfaRequired=true`, call `/login/verify-mfa`. Otherwise store `tokens`. |
| POST | `/api/v1/auth/agent/login/verify-mfa` | Public | none | none | `VerifyMfaRequest` | `200 ApiResponse<LoginResponse>` | `400 VALIDATION_FIELD_REQUIRED`, `401 MFA_INVALID`, `401 INVALID_CREDENTIALS`, `422 ACTOR_PENDING_KYC`, `422 ACTOR_SUSPENDED`, `422 ACTOR_CLOSED`, `429 TERMINAL_RATE_LIMIT` | Only valid after a login response with `mfaRequired=true`. |
| POST | `/api/v1/auth/agent/otp-request` | Public, deprecated | none | none | `AgentOtpRequest` | `200 ApiResponse<AgentOtpResponse>` or `410` | `410 LEGACY_OTP_LOGIN_REMOVED`, `401 INVALID_CREDENTIALS`, `422 ACTOR_PENDING_KYC`, `422 ACTOR_SUSPENDED`, `422 ACTOR_CLOSED`, `429 TERMINAL_RATE_LIMIT` | Default production config disables this endpoint. |
| POST | `/api/v1/auth/agent/otp-verify` | Public, deprecated | none | none | `AgentOtpVerifyRequest` | `200 ApiResponse<TokenResponse>` or `410` | `410 LEGACY_OTP_LOGIN_REMOVED`, `401 MFA_INVALID`, `401 INVALID_CREDENTIALS`, `429 TERMINAL_RATE_LIMIT` | Use only if legacy OTP is enabled by backend config. |
| POST | `/api/v1/auth/agent/refresh` | Public | none | none | `RefreshTokenRequest` | `200 ApiResponse<TokenResponse>` | `401 REFRESH_TOKEN_INVALID`, `401 INVALID_CREDENTIALS`, `422 ACTOR_PENDING_KYC`, `422 ACTOR_SUSPENDED`, `422 ACTOR_CLOSED`, `429 TERMINAL_RATE_LIMIT` | Replace the stored refresh token with the returned one. |
| POST | `/api/v1/auth/agent/logout` | Agent JWT | `Authorization` | none | none | `204 No Content` | `401 UNAUTHORIZED`, `401 TOKEN_EXPIRED`, `401 TOKEN_REVOKED`, `403 FORBIDDEN` | Clear local tokens after a successful call. |
| POST | `/api/v1/auth/agent/auth-pin/setup` | PIN_SETUP JWT | `Authorization` | none | `SetAuthPinRequest` | `204 No Content` | `400 VALIDATION_FIELD_REQUIRED`, `401 AUTH_INVALID_TOKEN`, `401 UNAUTHORIZED`, `422 AUTH_PIN_ALREADY_SET`, `422 AUTH_PIN_FORMAT` | Consumes the single-use `pinSetupToken` from a `pinSetupRequired` login response. |
| PUT | `/api/v1/auth/agent/auth-pin` | Agent JWT | `Authorization` | none | `ChangeAuthPinRequest` | `204 No Content` | `400 VALIDATION_FIELD_REQUIRED`, `401 AUTH_PIN_INVALID`, `401 UNAUTHORIZED`, `403 FORBIDDEN`, `422 AUTH_PIN_NOT_SET`, `422 AUTH_PIN_LOCKED`, `422 AUTH_PIN_FORMAT` | Requires the current PIN and the new PIN. |
| POST | `/api/v1/auth/agent/totp-setup` | Agent JWT | `Authorization` | none | none | `200 ApiResponse<TotpSetupResponse>` | `401 UNAUTHORIZED`, `403 FORBIDDEN` | Display `qrUri` as a QR code; treat `secret` as sensitive. |
| POST | `/api/v1/auth/agent/totp-confirm` | Agent JWT | `Authorization` | none | `TotpConfirmRequest` | `204 No Content` | `400 VALIDATION_FIELD_REQUIRED`, `401 MFA_INVALID`, `401 UNAUTHORIZED`, `403 FORBIDDEN` | Confirms the pending secret returned by setup. |
| DELETE | `/api/v1/auth/agent/totp-setup` | Agent JWT | `Authorization` | none | `TotpRevokeRequest` | `204 No Content` | `400 VALIDATION_FIELD_REQUIRED`, `401 MFA_INVALID`, `401 UNAUTHORIZED`, `403 FORBIDDEN` | Requires a current TOTP code as step-up verification. |

### 5.2 Profile, Balance, Limits, Summary

| Method | Path | Auth | Headers | Query | Request | Response | Primary errors | Frontend notes |
|---|---|---|---|---|---|---|---|---|
| GET | `/api/v1/agent/me` | Agent JWT | `Authorization` | none | none | `200 ApiResponse<AgentProfileResponse>` | `401 UNAUTHORIZED`, `403 FORBIDDEN`, `404 ACTOR_NOT_FOUND` | Use for profile and capability flags. |
| GET | `/api/v1/agent/balance` | Agent JWT | `Authorization` | none | none | `200 ApiResponse<AgentBalanceResponse>` | `401 UNAUTHORIZED`, `403 FORBIDDEN`, `404 WALLET_NOT_FOUND` | Shows the Agent wallet state in KMF. |
| GET | `/api/v1/agent/limits` | Agent JWT | `Authorization` | none | none | `200 ApiResponse<AgentLimitsResponse>` | `401 UNAUTHORIZED`, `403 FORBIDDEN`, `404 CONFIG_LIMIT_PROFILE_NOT_FOUND`, `404 ACTOR_NOT_FOUND` | Returns 404 when no limit profile is assigned. |
| GET | `/api/v1/agent/summary/daily` | Agent JWT | `Authorization` | none | none | `200 ApiResponse<AgentDailySummaryResponse>` | `401 UNAUTHORIZED`, `403 FORBIDDEN`, `404 ACTOR_NOT_FOUND` | Uses the current UTC calendar day. |

### 5.3 Transactions And Statements

| Method | Path | Auth | Headers | Query | Request | Response | Primary errors | Frontend notes |
|---|---|---|---|---|---|---|---|---|
| GET | `/api/v1/agent/transactions?cursor&limit&status&type&from&to` | Agent JWT | `Authorization` | `cursor`, `limit`, `status`, `type`, `from`, `to` | query | `200 PagedResponse<AgentPortalTransactionResponse>` | `400 VALIDATION_INVALID_FORMAT`, `401 UNAUTHORIZED`, `403 FORBIDDEN`, `404 ACTOR_NOT_FOUND` | Results are scoped to the authenticated Agent wallet. |
| GET | `/api/v1/agent/transactions/{id}` | Agent JWT | `Authorization` | none | none | `200 ApiResponse<AgentPortalTransactionResponse>` | `401 UNAUTHORIZED`, `403 FORBIDDEN`, `404 TRANSACTION_NOT_FOUND`, `404 ACTOR_NOT_FOUND` | Returns `403` if the transaction is not linked to the Agent wallet. |
| GET | `/api/v1/agent/statements?cursor&limit&from&to` | Agent JWT | `Authorization` | `cursor`, `limit`, `from`, `to` | query | `200 PagedResponse<AgentStatementEntryResponse>` | `400 VALIDATION_INVALID_FORMAT`, `401 UNAUTHORIZED`, `403 FORBIDDEN`, `404 ACTOR_NOT_FOUND` | Ledger entries are wallet-scoped, newest first. |

### 5.4 Cash Operations And Lookup

| Method | Path | Auth | Headers | Query | Request | Response | Primary errors | Frontend notes |
|---|---|---|---|---|---|---|---|---|
| GET | `/api/v1/agent/lookup?phoneCountryCode&phoneNumber` | Agent JWT | `Authorization` | `phoneCountryCode`, `phoneNumber` | query | `200 ApiResponse<CustomerLookupResponse>` | `400 VALIDATION_FIELD_REQUIRED`, `401 UNAUTHORIZED`, `403 FORBIDDEN`, `404 CUSTOMER_NOT_FOUND` | Minimal customer projection for pre-transaction confirmation. |
| GET | `/api/v1/agent/merchants/lookup?phoneCountryCode&phoneNumber` | Agent JWT | `Authorization` | `phoneCountryCode`, `phoneNumber` | query | `200 ApiResponse<MerchantLookupResponse>` | `400 VALIDATION_FIELD_REQUIRED`, `401 UNAUTHORIZED`, `403 FORBIDDEN`, `404 MERCHANT_NOT_FOUND` | Minimal merchant projection for pre-cash-out confirmation. Resolves `merchantId` for subsequent `cash-out`. |
| GET | `/api/v1/agent/cards/lookup?nfcUid` | Agent JWT | `Authorization` | `nfcUid` (14 hex chars) | query | `200 ApiResponse<CardLookupResponse>` | `400 VALIDATION_FIELD_REQUIRED`, `400 VALIDATION_ERROR`, `401 UNAUTHORIZED`, `403 FORBIDDEN`, `404 CARD_NOT_FOUND` | Minimal card projection by scanned NFC UID. Returns `cardId` and `customerId` (when linked) for subsequent report-lost / report-stolen / replace flows. Never returns PIN or auth-key material. |
| POST | `/api/v1/agent/cash-in` | Agent JWT | `Authorization`, `Idempotency-Key`, optional `X-Correlation-Id` | none | `AgentCashInRequest` | `200 ApiResponse<AgentTransactionResponse>` | `400 VALIDATION_FIELD_REQUIRED`, `400 VALIDATION_ERROR`, `401 UNAUTHORIZED`, `403 FORBIDDEN`, `404 AGENT_NOT_FOUND`, `404 CUSTOMER_NOT_FOUND`, `404 WALLET_NOT_FOUND`, `409 DUPLICATE_IDEMPOTENCY_KEY`, `422 WALLET_FROZEN`, `422 WALLET_SUSPENDED`, `422 WALLET_CLOSED`, `422 INSUFFICIENT_BALANCE`, `422 CONFIG_LIMIT_PROFILE_NOT_FOUND`, `422 CONFIG_RULE_INACTIVE`, `422 LIMIT_EXCEEDED` | Agent wallet is debited; customer wallet is credited. |
| POST | `/api/v1/agent/cash-out` | Agent JWT | `Authorization`, `Idempotency-Key`, optional `X-Correlation-Id` | none | `AgentCashOutRequest` | `200 ApiResponse<AgentCashOutResponse>` or `202 ApiResponse<AgentCashOutResponse>` | `400 VALIDATION_FIELD_REQUIRED`, `400 VALIDATION_ERROR`, `401 UNAUTHORIZED`, `403 FORBIDDEN`, `404 AGENT_NOT_FOUND`, `404 MERCHANT_NOT_FOUND`, `404 WALLET_NOT_FOUND`, `409 DUPLICATE_IDEMPOTENCY_KEY`, `422 ACTOR_SUSPENDED`, `422 CASH_OUT_NOT_ALLOWED`, `422 APPROVAL_REQUIRED`, `422 INSUFFICIENT_BALANCE`, `422 CONFIG_LIMIT_PROFILE_NOT_FOUND`, `422 CONFIG_RULE_INACTIVE`, `422 LIMIT_EXCEEDED` | `202` means a `LARGE_CASH_OUT` approval was created and no wallet movement happened yet. |

### 5.5 Customer Enrollment And KYC

| Method | Path | Auth | Headers | Query | Request | Response | Primary errors | Frontend notes |
|---|---|---|---|---|---|---|---|---|
| POST | `/api/v1/agent/customers/enroll` | Agent JWT | `Authorization`, optional `X-Correlation-Id` | none | `EnrollCustomerRequest` | `200 ApiResponse<EnrollCustomerResponse>` | `400 VALIDATION_FIELD_REQUIRED`, `401 UNAUTHORIZED`, `403 FORBIDDEN`, `404 AGENT_NOT_FOUND`, `422 ACTOR_PENDING_KYC`, `422 ACTOR_SUSPENDED`, `422 ACTOR_CLOSED`, `422 PHONE_ALREADY_IN_USE`, `422 NATIONAL_ID_ALREADY_IN_USE` | Creates customer, creates wallet, activates customer, applies `KYC_BASIC`. |
| POST | `/api/v1/agent/customers/{customerId}/kyc-documents` | Agent JWT | `Authorization`, `Content-Type: multipart/form-data` | multipart fields `documentType`, `file` | multipart | `200 ApiResponse<KycDocumentResponse>` | `400 VALIDATION_ERROR`, `401 UNAUTHORIZED`, `403 FORBIDDEN`, `404 AGENT_NOT_FOUND`, `404 CUSTOMER_NOT_FOUND`, `422 ACTOR_SUSPENDED` | `file` must be non-empty and at most 10 MB. |
| GET | `/api/v1/agent/customers/{customerId}/kyc-documents` | Agent JWT | `Authorization` | none | none | `200 ApiResponse<List<KycDocumentResponse>>` | `401 UNAUTHORIZED`, `403 FORBIDDEN`, `404 CUSTOMER_NOT_FOUND` | Lists all KYC documents stored for the customer. |

### 5.6 Cards And Card Stock

| Method | Path | Auth | Headers | Query | Request | Response | Primary errors | Frontend notes |
|---|---|---|---|---|---|---|---|---|
| GET | `/api/v1/agent/card-stock?status` | Agent JWT | `Authorization` | `status` | query | `200 PagedResponse<AgentCardStockView>` | `400 VALIDATION_INVALID_FORMAT`, `401 UNAUTHORIZED`, `403 FORBIDDEN` | Defaults to `ASSIGNED_TO_AGENT`; only the authenticated Agent stock is returned. |
| POST | `/api/v1/agent/card-sell` | Agent JWT | `Authorization`, `Idempotency-Key`, optional `X-Correlation-Id` | none | `AgentCardSellRequest` | `200 ApiResponse<AgentCardSaleResponse>` | `400 VALIDATION_FIELD_REQUIRED`, `400 VALIDATION_ERROR`, `401 UNAUTHORIZED`, `403 FORBIDDEN`, `404 AGENT_NOT_FOUND`, `404 CUSTOMER_NOT_FOUND`, `404 CARD_STOCK_NOT_FOUND`, `404 WALLET_NOT_FOUND`, `409 DUPLICATE_IDEMPOTENCY_KEY`, `422 ACTOR_PENDING_KYC`, `422 ACTOR_SUSPENDED`, `422 ACTOR_CLOSED`, `422 CARD_STOCK_WRONG_STATUS`, `422 CARD_STOCK_NOT_ASSIGNED_TO_AGENT`, `422 INSUFFICIENT_BALANCE`, `422 CONFIG_LIMIT_PROFILE_NOT_FOUND`, `422 CONFIG_RULE_INACTIVE`, `422 LIMIT_EXCEEDED` | Sells an assigned NFC card by scanned `nfcUid`. |
| POST | `/api/v1/agent/customers/{customerId}/cards/{cardId}/report-lost` | Agent JWT | `Authorization` | none | none | `200 ApiResponse<CardResponse>` | `401 UNAUTHORIZED`, `403 FORBIDDEN`, `404 AGENT_NOT_FOUND`, `404 CARD_NOT_FOUND`, `422 ACTOR_SUSPENDED` | Idempotent when the card is already `LOST`. |
| POST | `/api/v1/agent/customers/{customerId}/cards/{cardId}/report-stolen` | Agent JWT | `Authorization` | none | none | `200 ApiResponse<CardResponse>` | `401 UNAUTHORIZED`, `403 FORBIDDEN`, `404 AGENT_NOT_FOUND`, `404 CARD_NOT_FOUND`, `422 ACTOR_SUSPENDED` | Idempotent when the card is already `STOLEN`. |
| POST | `/api/v1/agent/customers/{customerId}/cards/{cardId}/replace` | Agent JWT | `Authorization`, `Idempotency-Key`, optional `X-Correlation-Id` | none | `AgentCardReplaceRequest` | `200 ApiResponse<AgentCardReplacementResponse>` | `400 VALIDATION_FIELD_REQUIRED`, `401 UNAUTHORIZED`, `403 FORBIDDEN`, `404 AGENT_NOT_FOUND`, `404 CARD_NOT_FOUND`, `404 CARD_STOCK_NOT_FOUND`, `404 WALLET_NOT_FOUND`, `409 DUPLICATE_IDEMPOTENCY_KEY`, `422 ACTOR_PENDING_KYC`, `422 ACTOR_SUSPENDED`, `422 ACTOR_CLOSED`, `422 CARD_NOT_ACTIVE`, `422 CARD_STOCK_WRONG_STATUS`, `422 CARD_STOCK_NOT_ASSIGNED_TO_AGENT`, `422 INSUFFICIENT_BALANCE`, `422 CONFIG_LIMIT_PROFILE_NOT_FOUND`, `422 CONFIG_RULE_INACTIVE`, `422 LIMIT_EXCEEDED` | Replaces a customer card using Agent-assigned stock and charges `replacementFee`. |

### 5.7 Commissions

| Method | Path | Auth | Headers | Query | Request | Response | Primary errors | Frontend notes |
|---|---|---|---|---|---|---|---|---|
| GET | `/api/v1/agent/commissions?cursor&limit&status` | Agent JWT | `Authorization` | `cursor`, `limit`, `status` | query | `200 PagedResponse<AgentCommissionResponse>` | `401 UNAUTHORIZED`, `403 FORBIDDEN` | `status` is optional; expected values are payout statuses. |

---

## 6. Request Schemas

Types:

- `uuid` = UUID string
- `instant` = ISO-8601 date-time string
- `date` = ISO local date, `YYYY-MM-DD`
- `long` = integer amount in KMF minor unit representation used by backend

### 6.1 Auth

```ts
LoginRequest = {
  phoneCountryCode: string; // digits, 1..5
  phoneNumber: string;      // digits, 4..15
  pin: string;              // digits, 4..8
}

VerifyMfaRequest = {
  challengeId: uuid;
  code: string;             // exactly 6 digits
}

AgentOtpRequest = {
  phoneCountryCode: string; // max 10
  phoneNumber: string;      // digits only, max 20
}

AgentOtpVerifyRequest = {
  challengeId: uuid;
  otpCode: string;          // exactly 6 digits
}

RefreshTokenRequest = {
  refreshToken: string;
}

SetAuthPinRequest = {
  pin: string;              // digits, 4..8
}

ChangeAuthPinRequest = {
  currentPin: string;       // digits, 4..8
  newPin: string;           // digits, 4..8
}

TotpConfirmRequest = {
  code: string;             // exactly 6 digits
}

TotpRevokeRequest = {
  code: string;             // exactly 6 digits
}
```

### 6.2 Profile And Read Queries

```ts
AgentTransactionListQuery = {
  cursor?: string;
  limit?: int;              // default 20, clamped 1..100
  status?: string;          // TransactionStatus name
  type?: string;            // TransactionType name
  from?: instant;
  to?: instant;
}

AgentStatementQuery = {
  cursor?: string;
  limit?: int;              // default 20, clamped 1..100
  from?: instant;
  to?: instant;
}

AgentCommissionQuery = {
  cursor?: string;
  limit?: int;              // default 20, clamped 1..100
  status?: string;          // PayoutStatus name
}

AgentCardStockQuery = {
  status?: CardStockStatus; // default ASSIGNED_TO_AGENT
}

CustomerLookupQuery = {
  phoneCountryCode: string;
  phoneNumber: string;
}
```

### 6.3 Customer Enrollment And KYC

```ts
EnrollCustomerRequest = {
  fullName: string;          // max 255
  dateOfBirth: date;         // must be past
  phoneCountryCode: string;  // digits, 1..5
  phoneNumber: string;       // digits, 4..15
  nationalIdNumber: string;  // max 100
  nationalIdType: string;    // max 50
  addressIsland?: string;    // max 100
  addressCity?: string;      // max 100
  addressDistrict?: string;  // max 100
}
```

KYC document upload is `multipart/form-data`:

```ts
KycDocumentUpload = {
  documentType: KycDocumentType;
  file: binary;              // required, non-empty, max 10 MB
}
```

### 6.4 Cash Operations

```ts
AgentCashInRequest = {
  customerId: uuid;
  amount: long;              // strictly positive
}

AgentCashOutRequest = {
  merchantId: uuid;
  amount: long;              // strictly positive
}
```

### 6.5 Cards

```ts
AgentCardSellRequest = {
  customerId: uuid;
  nfcUid: string;            // 14 hex characters
  cardPrice: long;           // strictly positive
}

AgentCardReplaceRequest = {
  stockId: uuid;
  replacementFee: long;      // strictly positive
}
```

### 6.6 Empty Request Bodies

For endpoints listed with `none`, send no JSON body.

---

## 7. Response Schemas

### 7.1 Auth

```ts
LoginResponse = {
  mfaRequired: boolean;
  challengeId?: uuid;
  mfaFactor?: string;        // "TOTP"
  tokens?: {
    accessToken: string;
    accessTokenExpiresAt: instant;
    refreshToken: string;
    refreshTokenExpiresAt: instant;
  };
}

TokenResponse = {
  tokenType: "Bearer";
  accessToken: string;
  accessTokenExpiresAt: instant;
  refreshToken: string;
  refreshTokenExpiresAt: instant;
}

AgentOtpResponse = {
  challengeId: uuid;
  expiresAt: instant;
}

TotpSetupResponse = {
  secret: string;
  qrUri: string;             // otpauth://totp/... URI
}
```

### 7.2 Profile, Balance, Limits, Summary

```ts
AgentProfileResponse = {
  id: uuid;
  externalRef: string;
  fullName: string;
  phoneCountryCode: string;
  phoneNumber: string;
  zone?: string;
  kycLevel: string;
  status: string;
  walletId?: uuid;
  limitProfileId?: uuid;
  canSellCards: boolean;
  canDoCashIn: boolean;
  canDoCashOut: boolean;
  floatAlertThreshold: long;
  contractRef?: string;
  createdAt: instant;
}

AgentBalanceResponse = {
  walletId: uuid;
  availableBalance: long;
  frozenBalance: long;
  currency: "KMF";
  walletStatus: string;
  updatedAt: instant;
}

AgentLimitsResponse = {
  limitProfileId: uuid;
  profileName: string;
  maxTransactionAmount?: long;
  minTransactionAmount?: long;
  maxDailyAmount?: long;
  maxWeeklyAmount?: long;
  maxMonthlyAmount?: long;
  maxDailyTransactionCount?: int;
  maxMonthlyTransactionCount?: int;
  requiredKycLevel: string;
}

AgentDailySummaryResponse = {
  agentId: uuid;
  periodStart: instant;
  periodEnd: instant;
  totalCompletedAmountToday: long;
  totalCompletedCountToday: int;
  commissionEarnedToday: long;
  currentBalance: long;
  floatAlertThreshold: long;
  belowFloatAlert: boolean;
}
```

### 7.3 Transactions And Statements

```ts
AgentTransactionResponse = {
  transactionId: uuid;
  status: TransactionStatus;
  requestedAmount: long;
  feeAmount: long;
  commissionAmount: long;
  netAmountToDestination: long;
  currency: "KMF";
  completedAt: instant;
  replayed: boolean;
}

AgentCashOutResponse = {
  transactionId?: uuid;
  status: string;            // TransactionStatus or ApprovalStatus
  approvalId?: uuid;
  requestedAmount?: long;
  feeAmount?: long;
  commissionAmount?: long;
  netAmountToDestination?: long;
  currency: "KMF";
  completedAt?: instant;
  replayed?: boolean;
}

AgentPortalTransactionResponse = {
  id: uuid;
  type: string;
  status: string;
  sourceWalletId: uuid;
  destinationWalletId: uuid;
  initiatorType: string;
  initiatorId: uuid;
  cardId?: uuid;
  terminalId?: uuid;
  requestedAmount: long;
  feeAmount: long;
  commissionAmount: long;
  netAmountToDestination: long;
  currency: "KMF";
  declineReason?: string;
  correlationId?: uuid;
  createdAt: instant;
  completedAt?: instant;
  declinedAt?: instant;
}

AgentStatementEntryResponse = {
  id: uuid;
  transactionId: uuid;
  entryType: EntryType;
  amount: long;
  runningBalance: long;
  currency: "KMF";
  description: string;
  postedAt: instant;
  globalSequence?: long;
}
```

### 7.4 Enrollment, KYC, Lookup

```ts
EnrollCustomerResponse = {
  customerId: uuid;
  externalRef: string;
  walletId: uuid;
}

KycDocumentResponse = {
  id: uuid;
  ownerActorType: ActorType;
  ownerActorId: uuid;
  documentType: KycDocumentType;
  contentHash: string;
  uploadedByActorType: ActorType;
  uploadedByActorId: uuid;
  uploadedAt: instant;
  status: KycDocumentStatus;
}

CustomerLookupResponse = {
  customerId: uuid;
  fullName: string;
  phoneCountryCode: string;
  phoneNumber: string;
  status: CustomerStatus;
  kycLevel: KycLevel;
}

MerchantLookupResponse = {
  merchantId: uuid;
  externalRef: string;            // e.g. "MCH-12345"
  businessName: string;
  phoneCountryCode: string;
  phoneNumber: string;
  status: MerchantStatus;
  kycLevel: KycLevel;
  canCashOut: boolean;
}

CardLookupResponse = {
  cardId: uuid;
  nfcUid: string;                 // 14 uppercase hex chars
  cardType: CardType;
  status: CardStatus;
  customerId: uuid | null;        // null when the card is not yet linked
  expiresAt: date | null;         // ISO-8601 calendar date
}
```

### 7.5 Cards, Stock, Commissions

```ts
AgentCardStockView = {
  id: uuid;
  nfcUid: string;
  internalCardNumber: string;
  batchRef: string;
  producedAt: date;
  assignedAt?: instant;
  status: CardStockStatus;
}

AgentCardSaleResponse = {
  transactionId: uuid;
  status: TransactionStatus;
  cardId: uuid;
  customerId: uuid;
  cardPrice: long;
  commissionAmount: long;
  completedAt: instant;
  replayed: boolean;
}

AgentCardReplacementResponse = {
  transactionId?: uuid;
  status?: TransactionStatus;
  newCardId: uuid;
  oldCardId: uuid;
  customerId: uuid;
  replacementFee?: long;
  commissionAmount?: long;
  completedAt?: instant;
  replayed: boolean;
}

CardResponse = {
  id: uuid;
  nfcUid?: string;
  internalCardNumber: string;
  walletId?: uuid;
  customerId?: uuid;
  cardType?: string;
  status: string;
  pinEnabled: boolean;
  issuedByAgentId?: uuid;
  issuedAt: instant;
  activatedAt?: instant;
  expiresAt: date;
  lastUsedAt?: instant;
  lastUsedTerminalId?: uuid;
  replacedByCardId?: uuid;
  replacementOfCardId?: uuid;
}

AgentCommissionResponse = {
  id: uuid;
  transactionId: uuid;
  appliedRuleId: uuid;
  amount: long;
  basisAmount: long;
  currency: "KMF";
  settlementMode: SettlementMode;
  status: PayoutStatus;
  paidAt?: instant;
  paymentTransactionId?: uuid;
  createdAt: instant;
}
```

---

## 8. Approval Payload Schemas

Agent endpoints do not return `ApprovalRequestResponse.payload`.

### 8.1 Payloads By Approval Type

No Agent-readable approval payload schema is part of the Agent frontend contract.

`POST /api/v1/agent/cash-out` can create a `LARGE_CASH_OUT` approval internally when a control threshold requires approval. The Agent response is limited to:

```ts
AgentCashOutResponse = {
  transactionId: null;
  status: "PENDING_APPROVAL";
  approvalId: uuid;
  requestedAmount: long;
  currency: "KMF";
}
```

The Agent frontend cannot approve, reject, list, or inspect approval payloads.

---

## 9. Enums

| Enum | Values |
|---|---|
| `ActorType` | `CUSTOMER`, `MERCHANT`, `AGENT`, `MERCHANT_OPERATOR`, `BACKOFFICE_USER`, `SYSTEM` |
| `AgentStatus` | `PENDING_KYC`, `ACTIVE`, `SUSPENDED`, `CLOSED` |
| `CustomerStatus` | `PENDING_KYC`, `ACTIVE`, `SUSPENDED`, `FROZEN`, `CLOSED` |
| `KycLevel` | `KYC_NONE`, `KYC_BASIC`, `KYC_VERIFIED`, `KYC_ENHANCED` |
| `TransactionType` | `CASH_IN`, `PAYMENT`, `CASH_OUT`, `CARD_SALE`, `AGENT_FUND_IN`, `AGENT_FUND_OUT`, `FEE_COLLECTION`, `COMMISSION_PAYOUT`, `REVERSAL`, `P2P_TRANSFER`, `MERCHANT_TO_MERCHANT`, `SERVICE_PAYMENT`, `CARD_REPLACEMENT`, `BILL_PROVIDER_SETTLEMENT`, `PLATFORM_REVENUE_WITHDRAWAL`, `PLATFORM_LIQUIDITY_TOP_UP` |
| `TransactionStatus` | `PENDING`, `AUTHORIZED`, `COMPLETED`, `DECLINED`, `EXPIRED`, `REVERSED` |
| `ApprovalStatus` | `PENDING_APPROVAL`, `APPROVED`, `REJECTED`, `EXPIRED` |
| `ChannelType` | `TERMINAL_NFC`, `TERMINAL_MANUAL`, `MOBILE_APP`, `AGENT_CHANNEL`, `WEB_APP`, `BACKOFFICE_UI`, `BACKOFFICE_JOB` |
| `WalletStatus` | `ACTIVE`, `FROZEN`, `SUSPENDED`, `CLOSED` |
| `EntryType` | `DEBIT`, `CREDIT` |
| `CardType` | `STANDARD`, `PREMIUM`, `CORPORATE` |
| `CardStatus` | `ISSUED`, `ACTIVE`, `BLOCKED`, `LOST`, `STOLEN`, `EXPIRED`, `CLOSED` |
| `CardStockStatus` | `IN_WAREHOUSE`, `ASSIGNED_TO_AGENT`, `SOLD`, `RETURNED`, `SPOILED` |
| `KycDocumentType` | `NATIONAL_ID`, `PASSPORT`, `PROOF_OF_ADDRESS`, `BUSINESS_LICENSE`, `OTHER` |
| `KycDocumentStatus` | `PENDING_REVIEW`, `ACCEPTED`, `REJECTED` |
| `OtpProviderType` | `TOTP`, `SMS` |
| `SettlementMode` | `IMMEDIATE`, `BATCH_DAILY`, `BATCH_WEEKLY` |
| `PayoutStatus` | `PENDING`, `PAID`, `FAILED`, `CANCELLED` |

---

## 10. Permissions

### 10.1 All Agent Permissions

Agent APIs do not use the backoffice `Permission` enum.

Endpoint authorization uses Spring Security role authority:

```text
ROLE_AGENT
```

### 10.2 Approval Permission Map

| Approval type | Agent frontend access |
|---|---|
| `LARGE_CASH_OUT` | Agent can create this indirectly via `POST /api/v1/agent/cash-out` when thresholds require approval; Agent cannot list, view, approve, or reject it |

No Agent approval decision endpoint exists.

### 10.3 Baseline Role Grants

| JWT actor type | Derived authority | Permission strings |
|---|---|---|
| `AGENT` | `ROLE_AGENT` | none |

Frontend gating should use the Agent profile fields for Agent-specific feature visibility:

- `canSellCards`
- `canDoCashIn`
- `canDoCashOut`
- `status`
- `limitProfileId`

Server-side role and business checks remain authoritative.

---

## 11. Operational Rules

### 11.1 Agent Frontend Flows

Login flow:

- Call `POST /api/v1/auth/agent/login` with phone and PIN.
- If `mfaRequired=false`, store `data.tokens`.
- If `mfaRequired=true`, prompt for the 6-digit TOTP code and call `POST /api/v1/auth/agent/login/verify-mfa`.
- Use `POST /api/v1/auth/agent/refresh` before access token expiry.
- Use `POST /api/v1/auth/agent/logout` to revoke the current access token and active refresh tokens.

MFA/TOTP flow:

- Call `POST /api/v1/auth/agent/totp-setup`.
- Display `qrUri` and/or `secret`.
- Call `POST /api/v1/auth/agent/totp-confirm` with a 6-digit code.
- After confirmation, future PIN-first logins return `mfaRequired=true`.
- Call `DELETE /api/v1/auth/agent/totp-setup` with a current TOTP code to revoke.

PIN management flow:

- Agents are created by Backoffice with no PIN; there is no default or temporary PIN.
- `POST /api/v1/auth/agent/login` returns `pinSetupRequired=true` together with a single-use `pinSetupToken` (10 min) when no PIN is configured.
- `POST /api/v1/auth/agent/auth-pin/setup` consumes that token and stores the chosen PIN. No full session is issued until this step succeeds.
- `PUT /api/v1/auth/agent/auth-pin` changes the PIN for an already-logged-in Agent and requires `currentPin`.
- A Backoffice-initiated forced reset (`POST /api/v1/backoffice/agents/{id}/auth-pin/reset`) returns the Agent to the `pinSetupRequired` state at next login.
- Legacy OTP can issue tokens only when backend config enables legacy OTP.

Cash-in flow:

- Optionally call `GET /api/v1/agent/lookup` to confirm the customer.
- Call `POST /api/v1/agent/cash-in` with `Idempotency-Key`.
- Success returns a completed transaction response with `replayed=false` or a prior transaction with `replayed=true`.
- Agent wallet is debited; customer wallet is credited; fee and commission are calculated server-side.

Cash-out flow:

- Call `POST /api/v1/agent/cash-out` with `Idempotency-Key`.
- `200` means the cash-out executed and wallet movement completed.
- `202` means a `LARGE_CASH_OUT` approval was created; the Agent frontend receives `approvalId` but cannot act on the approval.
- Merchant wallet is debited and Agent wallet is credited only on execution.

Customer onboarding flow:

- Call `POST /api/v1/agent/customers/enroll`.
- Upload KYC documents with `POST /api/v1/agent/customers/{customerId}/kyc-documents`.
- List uploaded documents with `GET /api/v1/agent/customers/{customerId}/kyc-documents`.
- If selling a card during onboarding, list assigned stock and then call `POST /api/v1/agent/card-sell`.

Cards flow:

- `GET /api/v1/agent/card-stock` lists the Agent's assigned stock.
- `POST /api/v1/agent/card-sell` consumes assigned stock, creates and activates the customer card, records a card sale transaction, and may record commission.
- `POST /api/v1/agent/customers/{customerId}/cards/{cardId}/report-lost` marks a customer card lost.
- `POST /api/v1/agent/customers/{customerId}/cards/{cardId}/report-stolen` marks a customer card stolen.
- `POST /api/v1/agent/customers/{customerId}/cards/{cardId}/replace` consumes assigned stock, closes the old card, activates the new card, records a card replacement transaction, and may record commission.

Commissions, history, profile, status, and limits:

- `GET /api/v1/agent/commissions` lists Agent commission payouts.
- `GET /api/v1/agent/transactions` lists wallet-scoped transactions.
- `GET /api/v1/agent/statements` lists wallet ledger entries.
- `GET /api/v1/agent/me`, `/balance`, `/limits`, and `/summary/daily` power Agent dashboard state.

### 11.2 Idempotency

The following Agent endpoints require `Idempotency-Key`:

- `POST /api/v1/agent/cash-in`
- `POST /api/v1/agent/cash-out`
- `POST /api/v1/agent/card-sell`
- `POST /api/v1/agent/customers/{customerId}/cards/{cardId}/replace`

Idempotency behavior:

- For completed transaction paths, a reused key returns the previous transaction response with `replayed=true`.
- A concurrent duplicate key can return `409 DUPLICATE_IDEMPOTENCY_KEY`; retrying with the same key can then hit the replay path.
- Cash-out responses that require approval return `202` with `approvalId`; no transaction response exists until the approval is executed.

No other Agent endpoint in this specification requires `Idempotency-Key`.

### 11.3 Frontend Gating

Use `GET /api/v1/agent/me` to drive Agent-specific UI visibility:

- Hide cash-in actions when `canDoCashIn=false`.
- Hide cash-out actions when `canDoCashOut=false`.
- Hide card sale and replacement actions when `canSellCards=false`.
- Treat any backend `403` or business-rule error as authoritative.

The backend also checks role, actor type, status, wallet state, limits, balances, card stock ownership, card ownership, and transaction thresholds.

### 11.4 Empty Bodies

For endpoints listed with `none`, send no JSON body.

For KYC document upload, send `multipart/form-data` with exactly these frontend-relevant fields:

- `documentType`
- `file`

---

## 12. Evidence Index

| Area | Source class |
|---|---|
| Auth Agent | `security.api.AgentAuthController`, `security.application.PinLoginService`, `security.application.AgentAuthenticationService` |
| Auth PIN | `security.api.AgentAuthPinController`, `security.application.AuthPinService`, `identity.domain.AuthPinState` |
| TOTP | `security.api.AgentTotpController`, `security.application.TotpEnrollmentService`, `security.domain.OtpProviderType` |
| JWT claims and authorities | `security.infrastructure.JwtService`, `security.infrastructure.JwtAuthenticationToken`, `security.domain.JwtPrincipal` |
| HTTP envelopes | `shared.infrastructure.web.ApiResponse`, `PagedResponse`, `ApiError`, `shared.infrastructure.exception.GlobalExceptionHandler` |
| Security and rate limit | `shared.infrastructure.config.SecurityConfig`, `shared.infrastructure.web.RateLimitingFilter`, `CorrelationIdFilter` |
| Agent profile reads | `agentportal.api.AgentMeController`, `AgentBalanceResponse`, `AgentProfileResponse`, `AgentReadService` |
| Agent limits | `agentportal.api.AgentLimitsController`, `AgentLimitsResponse`, `LimitEnforcementService` |
| Agent summary | `agentportal.api.AgentSummaryController`, `AgentDailySummaryResponse` |
| Agent transactions and statements | `agentportal.api.AgentTransactionListController`, `AgentStatementController`, `AgentPortalTransactionResponse`, `AgentStatementEntryResponse` |
| Agent commissions | `agentportal.api.AgentCommissionController`, `AgentCommissionResponse`, `fee.domain.CommissionPayout` |
| Cash-in and cash-out | `transaction.api.AgentTransactionController`, `CashInUseCase`, `CashOutSubmissionUseCase`, `CashOutUseCase` |
| Customer lookup | `transaction.api.AgentTransactionController`, `CustomerLookupResponse` |
| Merchant lookup | `transaction.api.AgentTransactionController`, `MerchantLookupResponse` |
| Card lookup | `transaction.api.AgentTransactionController`, `CardLookupResponse` |
| Customer enrollment | `identity.api.AgentEnrollmentController`, `EnrollCustomerUseCase`, `EnrollCustomerRequest`, `EnrollCustomerResponse` |
| KYC documents | `kyc.api.AgentKycController`, `KycDocumentResponse`, `StoreKycDocumentUseCase` |
| Card stock | `card.api.AgentCardStockController`, `card.domain.CardStock`, `CardStockStatus` |
| Card sale | `transaction.api.AgentTransactionController`, `CardSaleUseCase`, `AgentCardSellRequest`, `AgentCardSaleResponse` |
| Card report lost/stolen | `card.api.AgentCardController`, `card.application.AgentCardService`, `backoffice.api.dto.CardResponse` |
| Card replacement | `transaction.api.AgentCardReplacementController`, `CardReplacementUseCase`, `AgentCardReplaceRequest`, `AgentCardReplacementResponse` |
| Enums | `identity.domain.AgentStatus`, `CustomerStatus`, `shared.domain.TransactionType`, `transaction.domain.TransactionStatus`, `card.domain.CardStatus`, `card.domain.CardStockStatus`, `kyc.domain.KycDocumentType`, `kyc.domain.KycDocumentStatus`, `fee.domain.PayoutStatus`, `fee.domain.SettlementMode` |

---

End of document. All content above is derived from the current Lipa backend codebase only.
