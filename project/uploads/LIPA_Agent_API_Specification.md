# LIPA - Specification API Agent

**Version :** 1.0 | **Source :** analyse du backend KomoPay + `docs/LIPA_Frontend_Specification.md` | **Date :** 2026-05-04  
**Statut :** specification cible pour le portail Agent. Ne pas ajouter de fonctionnalite non listee ici.

---

## Table des matieres

1. [Portee](#1-portee)
2. [Contrat HTTP commun](#2-contrat-http-commun)
3. [Acteur Agent et preconditions](#3-acteur-agent-et-preconditions)
4. [Parcours fonctionnels](#4-parcours-fonctionnels)
5. [Catalogue exhaustif des endpoints Agent](#5-catalogue-exhaustif-des-endpoints-agent)
6. [Schemas de donnees](#6-schemas-de-donnees)
7. [Regles metier et effets financiers](#7-regles-metier-et-effets-financiers)
8. [Erreurs, securite et idempotence](#8-erreurs-securite-et-idempotence)
9. [Fonctionnalites absentes cote API Agent](#9-fonctionnalites-absentes-cote-api-agent)
10. [Index des preuves code](#10-index-des-preuves-code)

---

## 1. Portee

Ce document couvre uniquement ce qu'un acteur `AGENT` peut faire via l'API :

- s'authentifier par telephone + OTP/TOTP ;
- gerer sa session et son enrollement TOTP ;
- lire son profil, son solde, son resume quotidien, ses transactions, son releve, ses commissions, ses limites et son stock de cartes ;
- rechercher un client par telephone ;
- enrôler un nouveau client ;
- uploader et consulter les documents KYC d'un client ;
- effectuer un cash-in client ;
- soumettre/executer un cash-out marchand ;
- vendre une carte NFC a un client ;
- declarer une carte client perdue ou volee ;
- remplacer une carte client avec une carte de son stock.

Hors portee :

- les endpoints backoffice qui creent, activent, suspendent, financent ou parametrent un agent ;
- les endpoints customer, merchant, terminal et backoffice, sauf lorsqu'ils expliquent un effet indirect d'une action agent ;
- toute fonctionnalite non exposee par un controller `AGENT`.

---

## 2. Contrat HTTP commun

### 2.1 Base URL et formats

Tous les endpoints sont sous `/api/v1`. Les dates/heures sont serialisees en ISO-8601. Les montants sont des entiers `long` en KMF, sans sous-unite. Les identifiants sont des UUID.

Reponse succes standard :

```json
{
  "data": {},
  "timestamp": "2026-05-04T10:15:30Z"
}
```

Reponse paginee :

```json
{
  "data": [],
  "pagination": {
    "nextCursor": "string|null",
    "hasMore": false,
    "limit": 20
  }
}
```

Reponse erreur applicative :

```json
{
  "error": {
    "code": "ERROR_CODE",
    "message": "Human readable message",
    "details": [],
    "correlationId": "uuid",
    "timestamp": "2026-05-04T10:15:30Z"
  }
}
```

Exceptions confirmees :

- `204 No Content` ne renvoie aucun body.
- Certaines erreurs emises avant le controller par Spring Security/JWT renvoient `ApiError` directement, sans wrapper `{ "error": ... }`.
- Les erreurs de validation renvoient `details` comme tableau de chaines, par exemple `"amount: amount must be strictly positive"`.

### 2.2 Headers

| Header | Obligatoire | Utilisation |
|---|---:|---|
| `Authorization: Bearer <accessToken>` | Oui sur tous les endpoints proteges | Session AGENT. Non requis pour OTP request, OTP verify et refresh. |
| `Idempotency-Key` | Oui sur les mutations financieres agent | `cash-in`, `cash-out`, `card-sell`, `card replacement`. |
| `X-Correlation-Id` | Optionnel | UUID de tracabilite. Le comportement en cas de valeur invalide varie selon l'endpoint, voir sections detaillees. |
| `User-Agent` | Auto navigateur/app | Capture lors de `otp-verify` et `refresh`. |
| `Content-Type: application/json` | Oui pour JSON | Toutes les requetes JSON. |
| `Content-Type: multipart/form-data` | Oui pour upload KYC | Upload document KYC. |

### 2.3 Pagination cursor

Endpoints concernes :

- `GET /api/v1/agent/statements`
- `GET /api/v1/agent/transactions`
- `GET /api/v1/agent/commissions`

Regles :

- `cursor` est opaque : le client ne doit jamais le parser ni le construire.
- `limit` est clamp entre 1 et 100 ; defaut `20`.
- Les listes sont triees du plus recent au plus ancien.
- `hasMore=false` ou `nextCursor=null` signifie qu'il n'y a plus de page.
- Un cursor invalide est traite comme "pas de cursor" par le backend.

`GET /api/v1/agent/card-stock` utilise `PagedResponse.last(...)` : resultat en une seule page, `hasMore=false`.

---

## 3. Acteur Agent et preconditions

### 3.1 Statuts agent

| Statut | Signification API |
|---|---|
| `PENDING_KYC` | Compte cree mais non operationnel. Login refuse. |
| `ACTIVE` | Compte operationnel. Login et operations possibles selon les autres controles. |
| `SUSPENDED` | Login/refresh refuse. Plusieurs operations ecrites refusent explicitement ce statut. |
| `CLOSED` | Compte ferme. Login/refresh refuse. Aucune reactivation cote agent. |

L'activation d'un agent est une action backoffice et exige `KYC_ENHANCED`. Un agent actif possede normalement un wallet agent en KMF.

### 3.2 JWT Agent

Le JWT d'acces agent contient au minimum :

| Claim | Valeur |
|---|---|
| `sub` | `agentId` |
| `act` | `AGENT` |
| `jti` | identifiant du token |
| `iat`, `exp` | emission et expiration |

Pas de `perms[]` pour les agents. L'autorisation endpoint repose sur `ROLE_AGENT` et sur les controles metier serveur.

TTL :

- access token agent : 8 heures ;
- refresh token agent : 30 jours ;
- challenge OTP/TOTP : 5 minutes.

### 3.3 Capacites exposees dans le profil

`GET /api/v1/agent/me` expose :

- `canSellCards`
- `canDoCashIn`
- `canDoCashOut`

Comportement backend confirme :

- `cash-out` verifie explicitement `canDoCashOut`.
- `cash-in`, `card-sell` et `card-replace` exposent/beneficient de ces flags cote UI, mais le code actuel ne verifie pas directement `canDoCashIn`/`canSellCards`. `cash-in` enforce surtout wallet source actif, limites et solde ; `card-sell`/`card-replace` enforce agent actif, wallet actif, stock assigne, limites et solde. Ne pas masquer cette nuance dans les tests fonctionnels.

---

## 4. Parcours fonctionnels

### 4.1 Connexion agent

1. L'agent saisit `phoneCountryCode` + `phoneNumber`.
2. `POST /api/v1/auth/agent/otp-request`.
3. Si l'agent a TOTP actif, le backend cree un challenge TOTP sans SMS. Sinon il cree un challenge SMS.
4. L'agent saisit un code 6 chiffres.
5. `POST /api/v1/auth/agent/otp-verify`.
6. L'app stocke `accessToken`, `refreshToken`, dates d'expiration et redirige vers le dashboard.

### 4.2 Dashboard agent

Appels minimum :

- `GET /api/v1/agent/me`
- `GET /api/v1/agent/balance`
- `GET /api/v1/agent/summary/daily`

Le resume quotidien est calcule sur la journee UTC courante.

### 4.3 Cash-in client

1. Chercher le client : `GET /api/v1/agent/lookup`.
2. Confirmer visuellement le client.
3. Generer un `Idempotency-Key`.
4. `POST /api/v1/agent/cash-in`.

Effet financier : le wallet agent est debite, le wallet client est credite, les frais vont a `SYSTEM_REVENUE` si la regle de frais l'exige. La commission agent est calculee et peut etre payee immediatement ou stockee pour settlement.

### 4.4 Cash-out marchand

1. L'agent saisit le `merchantId` et le montant.
2. Generer un `Idempotency-Key`.
3. `POST /api/v1/agent/cash-out`.
4. Si le seuil de controle n'exige pas d'approbation : `200 OK`, transaction executee.
5. Si le seuil exige une approbation : `202 Accepted`, retour d'un `approvalId`, pas de mutation wallet immediate.

Effet financier quand execute : le wallet marchand est debite, le wallet agent est credite. La remise de cash physique est hors API, mais le flux electronique compense l'agent.

### 4.5 Enrolement client par agent

1. `POST /api/v1/agent/customers/enroll` cree le client, le wallet client et applique `KYC_BASIC`.
2. `POST /api/v1/agent/customers/{customerId}/kyc-documents` ajoute les justificatifs.
3. `POST /api/v1/agent/card-sell` vend/active une carte depuis le stock agent si necessaire.

### 4.6 Carte perdue/volee et remplacement

- Perte : `POST /api/v1/agent/customers/{customerId}/cards/{cardId}/report-lost`.
- Vol : `POST /api/v1/agent/customers/{customerId}/cards/{cardId}/report-stolen`.
- Remplacement payant : `POST /api/v1/agent/customers/{customerId}/cards/{cardId}/replace`.

Le remplacement consomme une entree de stock `ASSIGNED_TO_AGENT`, cree une nouvelle carte active, ferme l'ancienne carte et execute une transaction `CARD_REPLACEMENT`.

---

## 5. Catalogue exhaustif des endpoints Agent

### 5.1 Authentification et session

#### `POST /api/v1/auth/agent/otp-request`

Public. Demande un challenge OTP/TOTP.

Body :

```json
{
  "phoneCountryCode": "269",
  "phoneNumber": "3210000"
}
```

Validations :

| Champ | Regle |
|---|---|
| `phoneCountryCode` | requis, max 10 |
| `phoneNumber` | requis, chiffres uniquement, max 20 |

Succes `200` :

```json
{
  "data": {
    "challengeId": "uuid",
    "expiresAt": "2026-05-04T10:20:30Z"
  },
  "timestamp": "2026-05-04T10:15:30Z"
}
```

Regles :

- refuse si telephone inconnu, agent `PENDING_KYC`, `SUSPENDED` ou `CLOSED`;
- si TOTP est deja confirme, aucun SMS n'est envoye.

#### `POST /api/v1/auth/agent/otp-verify`

Public. Verifie le code 6 chiffres et retourne une paire de tokens.

Body :

```json
{
  "challengeId": "uuid",
  "otpCode": "123456"
}
```

Validations :

| Champ | Regle |
|---|---|
| `challengeId` | UUID requis |
| `otpCode` | requis, exactement 6 chiffres |

Succes `200` :

```json
{
  "data": {
    "tokenType": "Bearer",
    "accessToken": "jwt",
    "accessTokenExpiresAt": "2026-05-04T18:15:30Z",
    "refreshToken": "opaque-token",
    "refreshTokenExpiresAt": "2026-06-03T10:15:30Z"
  },
  "timestamp": "2026-05-04T10:15:30Z"
}
```

Regles :

- challenge consomme apres succes ;
- code invalide ou challenge expire : `MFA_INVALID`;
- agent devenu non utilisable entre request et verify : erreur metier de statut.

#### `POST /api/v1/auth/agent/refresh`

Public. Rotation du refresh token.

Body :

```json
{
  "refreshToken": "opaque-token"
}
```

Succes `200` : meme schema que `TokenResponse`.

Regles :

- l'ancien refresh token est marque remplace ;
- reutilisation d'un refresh token deja remplace : tous les refresh tokens actifs de l'agent sont revoques ;
- agent `PENDING_KYC`, `SUSPENDED` ou `CLOSED` : refresh refuse.

#### `POST /api/v1/auth/agent/logout`

Protege `AGENT`.

Headers :

- `Authorization: Bearer <accessToken>`

Succes : `204 No Content`.

Effets :

- revoke le JWT courant jusqu'a son expiration naturelle ;
- revoke tous les refresh tokens actifs de l'agent.

### 5.2 TOTP Agent

#### `POST /api/v1/auth/agent/totp-setup`

Protege `AGENT`. Genere un secret TOTP en attente.

Succes `200` :

```json
{
  "data": {
    "secret": "BASE32SECRET",
    "qrUri": "otpauth://totp/..."
  },
  "timestamp": "2026-05-04T10:15:30Z"
}
```

Le client doit afficher `qrUri` sous forme de QR code et traiter `secret` comme une credential sensible.

#### `POST /api/v1/auth/agent/totp-confirm`

Protege `AGENT`. Active le secret TOTP en attente.

Body :

```json
{ "code": "123456" }
```

Validation : `code` requis, exactement 6 chiffres.

Succes : `204 No Content`.

#### `DELETE /api/v1/auth/agent/totp-setup`

Protege `AGENT`. Revoque TOTP et remet l'agent en fallback SMS OTP.

Body :

```json
{ "code": "123456" }
```

Validation : code TOTP courant requis, exactement 6 chiffres.

Succes : `204 No Content`.

### 5.3 Lecture portail agent

#### `GET /api/v1/agent/me`

Protege `AGENT`. Retourne le profil agent.

Succes `200` : `AgentProfileResponse`.

#### `GET /api/v1/agent/balance`

Protege `AGENT`. Retourne le solde du wallet agent KMF.

Succes `200` : `AgentBalanceResponse`.

Erreurs :

- `WALLET_NOT_FOUND` si aucun wallet agent n'est provisionne.

#### `GET /api/v1/agent/summary/daily`

Protege `AGENT`. Retourne les volumes de la journee UTC courante.

Succes `200` : `AgentDailySummaryResponse`.

#### `GET /api/v1/agent/statements`

Protege `AGENT`. Releve ledger du wallet agent.

Query params :

| Param | Type | Defaut | Description |
|---|---|---:|---|
| `cursor` | string | null | cursor opaque |
| `limit` | int | 20 | clamp 1..100 |
| `from` | Instant | null | filtre date debut |
| `to` | Instant | null | filtre date fin |

Succes `200` : `PagedResponse<AgentStatementEntryResponse>`.

#### `GET /api/v1/agent/transactions`

Protege `AGENT`. Transactions dont le wallet agent est source ou destination.

Query params :

| Param | Type | Defaut | Description |
|---|---|---:|---|
| `cursor` | string | null | cursor opaque |
| `limit` | int | 20 | clamp 1..100 |
| `status` | string | null | filtre status transaction |
| `type` | string | null | filtre type transaction |
| `from` | Instant | null | filtre date debut |
| `to` | Instant | null | filtre date fin |

Succes `200` : `PagedResponse<AgentPortalTransactionResponse>`.

#### `GET /api/v1/agent/transactions/{id}`

Protege `AGENT`. Detail d'une transaction.

Path params :

- `id` : UUID transaction.

Succes `200` : `AgentPortalTransactionResponse`.

Regles :

- refuse `403` si la transaction n'appartient pas au wallet de l'agent.

#### `GET /api/v1/agent/commissions`

Protege `AGENT`. Historique de commissions/payouts.

Query params :

| Param | Type | Defaut | Description |
|---|---|---:|---|
| `cursor` | string | null | cursor opaque |
| `limit` | int | 20 | clamp 1..100 |
| `status` | string | null | `PENDING`, `PAID`, `FAILED`, `CANCELLED` |

Succes `200` : `PagedResponse<AgentCommissionResponse>`.

#### `GET /api/v1/agent/limits`

Protege `AGENT`. Retourne le profil de limites assigne a l'agent.

Succes `200` : `AgentLimitsResponse`.

Erreurs :

- `CONFIG_LIMIT_PROFILE_NOT_FOUND` / `404` si aucun profil de limites n'est assigne.

Important : ce endpoint retourne la configuration de limites, pas les compteurs d'usage courant.

#### `GET /api/v1/agent/card-stock`

Protege `AGENT`. Retourne le stock de cartes de l'agent.

Query params :

| Param | Type | Defaut |
|---|---|---|
| `status` | `CardStockStatus` | `ASSIGNED_TO_AGENT` |

Succes `200` : `PagedResponse<AgentCardStockView>` avec `hasMore=false`.

Regles :

- ne retourne que les cartes assignees a l'agent authentifie ;
- le client peut demander d'autres statuts (`SOLD`, `RETURNED`, etc.), mais toujours scopes a cet agent.

### 5.4 Lookup client

#### `GET /api/v1/agent/lookup`

Protege `AGENT`. Recherche un client par telephone.

Query params :

| Param | Type | Requis |
|---|---|---:|
| `phoneCountryCode` | string | oui |
| `phoneNumber` | string | oui |

Succes `200` : `CustomerLookupResponse`.

Erreurs :

- `CUSTOMER_NOT_FOUND` si aucun client ne correspond.

La reponse est volontairement minimale : pas d'adresse, pas de numero national ID.

### 5.5 Enrolement client

#### `POST /api/v1/agent/customers/enroll`

Protege `AGENT`. Cree un client + wallet et applique `KYC_BASIC`.

Headers :

- `Authorization`
- `X-Correlation-Id` optionnel. Si absent ou invalide, le backend genere un UUID aleatoire.

Body :

```json
{
  "fullName": "Ali Madi",
  "dateOfBirth": "1990-01-15",
  "phoneCountryCode": "269",
  "phoneNumber": "3210000",
  "nationalIdNumber": "ABC123456",
  "nationalIdType": "NATIONAL_ID",
  "addressIsland": "Grande Comore",
  "addressCity": "Moroni",
  "addressDistrict": "Centre"
}
```

Validations :

| Champ | Regle |
|---|---|
| `fullName` | requis, max 255 |
| `dateOfBirth` | requis, date passee |
| `phoneCountryCode` | requis, `\d{1,5}` |
| `phoneNumber` | requis, `\d{4,15}` |
| `nationalIdNumber` | requis, max 100 |
| `nationalIdType` | requis, max 50 |
| `addressIsland` | optionnel, max 100 |
| `addressCity` | optionnel, max 100 |
| `addressDistrict` | optionnel, max 100 |

Succes `200` :

```json
{
  "data": {
    "customerId": "uuid",
    "externalRef": "CUST-00001",
    "walletId": "uuid"
  },
  "timestamp": "2026-05-04T10:15:30Z"
}
```

Regles :

- agent doit etre `ACTIVE`;
- telephone client unique ;
- numero national ID unique ;
- cree un wallet client actif en KMF avec solde zero ;
- applique `KYC_BASIC`;
- assigne le profil `CUSTOMER_RESTRICTED` s'il existe.

### 5.6 KYC documents client

#### `POST /api/v1/agent/customers/{customerId}/kyc-documents`

Protege `AGENT`. Upload un document KYC pour un client.

Content-Type : `multipart/form-data`.

Path params :

- `customerId` : UUID client.

Form fields :

| Champ | Type | Regle |
|---|---|---|
| `documentType` | string | `NATIONAL_ID`, `PASSPORT`, `PROOF_OF_ADDRESS`, `BUSINESS_LICENSE`, `OTHER` |
| `file` | binary | requis, non vide, max 10 MB |

Succes `200` : `KycDocumentResponse`.

Regles :

- agent doit etre `ACTIVE`;
- client doit exister ;
- contenu stocke via `KycStoragePort`;
- `contentHash` SHA-256 calcule ;
- statut initial document : `PENDING_REVIEW`.

#### `GET /api/v1/agent/customers/{customerId}/kyc-documents`

Protege `AGENT`. Liste les documents KYC du client.

Path params :

- `customerId` : UUID client.

Succes `200` :

```json
{
  "data": [ { "id": "uuid" } ],
  "timestamp": "2026-05-04T10:15:30Z"
}
```

Regles :

- client doit exister ;
- la liste n'est pas paginee.

### 5.7 Transactions operationnelles

#### `POST /api/v1/agent/cash-in`

Protege `AGENT`. Depot de valeur sur un wallet client.

Headers :

- `Authorization`
- `Idempotency-Key` obligatoire
- `X-Correlation-Id` optionnel, doit etre un UUID valide si fourni

Body :

```json
{
  "customerId": "uuid",
  "amount": 10000
}
```

Validations :

| Champ | Regle |
|---|---|
| `customerId` | UUID requis |
| `amount` | entier strictement positif |

Succes `200` : `AgentOperationTransactionResponse`.

Regles :

- wallet agent source doit etre `ACTIVE`;
- wallet client destination ne doit pas etre `CLOSED`;
- limite sortante agent enforcee ;
- limite entrante client enforcee si applicable ;
- solde agent suffisant pour le debit effectif ;
- fees et commissions calculees avant autorisation ;
- transaction stamp `ChannelType.AGENT_CHANNEL`.

#### `POST /api/v1/agent/cash-out`

Protege `AGENT`. Cash-out marchand via agent.

Headers :

- `Authorization`
- `Idempotency-Key` obligatoire
- `X-Correlation-Id` optionnel, doit etre un UUID valide si fourni

Body :

```json
{
  "merchantId": "uuid",
  "amount": 25000
}
```

Validations :

| Champ | Regle |
|---|---|
| `merchantId` | UUID requis |
| `amount` | entier strictement positif |

Succes execution immediate `200` : `AgentCashOutResponse` avec `transactionId`.

Succes avec approbation requise `202` :

```json
{
  "data": {
    "transactionId": null,
    "status": "PENDING_APPROVAL",
    "approvalId": "uuid",
    "requestedAmount": 25000,
    "feeAmount": null,
    "commissionAmount": null,
    "netAmountToDestination": null,
    "currency": "KMF",
    "completedAt": null,
    "replayed": null
  },
  "timestamp": "2026-05-04T10:15:30Z"
}
```

Regles :

- le marchand doit exister ;
- le seuil de controle peut exiger `LARGE_CASH_OUT`;
- s'il y a approbation, aucun wallet n'est mute avant approbation backoffice ;
- une seule approbation `LARGE_CASH_OUT` pending est autorisee par marchand ;
- en execution immediate : agent `ACTIVE`, `canDoCashOut=true`, marchand operationnel et `canCashOut=true`, wallets valides, limites enforcees, solde marchand suffisant.

#### `POST /api/v1/agent/card-sell`

Protege `AGENT`. Vend et active une carte NFC pour un client.

Headers :

- `Authorization`
- `Idempotency-Key` obligatoire
- `X-Correlation-Id` optionnel, doit etre un UUID valide si fourni

Body :

```json
{
  "customerId": "uuid",
  "nfcUid": "04AABBCCDDEEFF",
  "cardPrice": 5000
}
```

Validations :

| Champ | Regle |
|---|---|
| `customerId` | UUID requis |
| `nfcUid` | requis, exactement 14 caracteres hexadecimaux |
| `cardPrice` | entier strictement positif |

Succes `200` : `AgentCardSaleResponse`.

Regles :

- agent doit etre `ACTIVE`;
- client doit exister, ne pas etre `CLOSED`, et posseder un wallet ;
- `nfcUid` doit correspondre a une entree `CardStock`;
- stock doit etre `ASSIGNED_TO_AGENT` et assigne a l'agent appelant ;
- wallet agent doit etre `ACTIVE`;
- solde agent suffisant pour remitter `cardPrice` ;
- limite sortante agent enforcee ;
- cree une carte `STANDARD`, l'active, la lie au client, marque le stock `SOLD`;
- transaction `CARD_SALE`, debit agent, credit `SYSTEM_REVENUE`.

#### `POST /api/v1/agent/customers/{customerId}/cards/{cardId}/replace`

Protege `AGENT`. Remplacement payant d'une carte client.

Headers :

- `Authorization`
- `Idempotency-Key` obligatoire
- `X-Correlation-Id` optionnel. Si invalide, il est ignore et transmis comme `null`.

Path params :

| Param | Type |
|---|---|
| `customerId` | UUID |
| `cardId` | UUID ancienne carte |

Body :

```json
{
  "stockId": "uuid",
  "replacementFee": 5000
}
```

Validations :

| Champ | Regle |
|---|---|
| `stockId` | UUID requis |
| `replacementFee` | entier strictement positif |

Succes `200` : `AgentCardReplacementResponse`.

Regles :

- agent doit etre `ACTIVE`;
- ancienne carte doit appartenir au `customerId`;
- ancienne carte ne doit pas etre terminale (`CLOSED`, `EXPIRED`);
- si l'ancienne carte a deja `replacedByCardId`, le backend retourne la carte de remplacement existante sans creer de nouvelle transaction ;
- stock doit etre `ASSIGNED_TO_AGENT` et assigne a l'agent appelant ;
- wallet agent doit etre `ACTIVE`;
- solde agent suffisant pour `replacementFee`;
- limite sortante agent enforcee ;
- cree une nouvelle carte active, ferme l'ancienne, marque le stock `SOLD`;
- transaction `CARD_REPLACEMENT`, debit agent, credit `SYSTEM_REVENUE`.

### 5.8 Operations carte non financieres

#### `POST /api/v1/agent/customers/{customerId}/cards/{cardId}/report-lost`

Protege `AGENT`. Marque une carte client comme perdue.

Path params :

- `customerId` : UUID client.
- `cardId` : UUID carte.

Succes `200` : `CardResponse`.

Regles :

- agent doit etre `ACTIVE`;
- carte doit appartenir au client du path ;
- operation idempotente si la carte est deja `LOST`;
- impossible si la carte est terminale (`CLOSED` ou `EXPIRED`).

#### `POST /api/v1/agent/customers/{customerId}/cards/{cardId}/report-stolen`

Protege `AGENT`. Marque une carte client comme volee.

Succes `200` : `CardResponse`.

Regles :

- agent doit etre `ACTIVE`;
- carte doit appartenir au client du path ;
- operation idempotente si la carte est deja `STOLEN`;
- impossible si la carte est terminale (`CLOSED` ou `EXPIRED`).

---

## 6. Schemas de donnees

### 6.1 `AgentProfileResponse`

```json
{
  "id": "uuid",
  "externalRef": "AGENT-00001",
  "fullName": "Agent Name",
  "phoneCountryCode": "269",
  "phoneNumber": "3210000",
  "zone": "Moroni",
  "kycLevel": "KYC_ENHANCED",
  "status": "ACTIVE",
  "walletId": "uuid",
  "limitProfileId": "uuid|null",
  "canSellCards": true,
  "canDoCashIn": true,
  "canDoCashOut": true,
  "floatAlertThreshold": 0,
  "contractRef": "string|null",
  "createdAt": "Instant"
}
```

### 6.2 `AgentBalanceResponse`

```json
{
  "walletId": "uuid",
  "availableBalance": 100000,
  "frozenBalance": 0,
  "currency": "KMF",
  "walletStatus": "ACTIVE",
  "updatedAt": "Instant"
}
```

### 6.3 `AgentDailySummaryResponse`

```json
{
  "agentId": "uuid",
  "periodStart": "Instant",
  "periodEnd": "Instant",
  "totalCompletedAmountToday": 100000,
  "totalCompletedCountToday": 12,
  "commissionEarnedToday": 2500,
  "currentBalance": 50000,
  "floatAlertThreshold": 10000,
  "belowFloatAlert": false
}
```

### 6.4 `AgentStatementEntryResponse`

```json
{
  "id": "uuid",
  "transactionId": "uuid",
  "entryType": "DEBIT|CREDIT",
  "amount": 10000,
  "runningBalance": 90000,
  "currency": "KMF",
  "description": "string",
  "postedAt": "Instant",
  "globalSequence": 12345
}
```

### 6.5 `AgentPortalTransactionResponse`

Utilise par les endpoints de lecture `/api/v1/agent/transactions`.

```json
{
  "id": "uuid",
  "type": "CASH_IN",
  "status": "COMPLETED",
  "sourceWalletId": "uuid",
  "destinationWalletId": "uuid",
  "initiatorType": "AGENT",
  "initiatorId": "uuid",
  "cardId": "uuid|null",
  "terminalId": "uuid|null",
  "requestedAmount": 10000,
  "feeAmount": 0,
  "commissionAmount": 0,
  "netAmountToDestination": 10000,
  "currency": "KMF",
  "declineReason": "string|null",
  "correlationId": "uuid|null",
  "createdAt": "Instant",
  "completedAt": "Instant|null",
  "declinedAt": "Instant|null"
}
```

### 6.6 `AgentOperationTransactionResponse`

Utilise par `cash-in`.

```json
{
  "transactionId": "uuid",
  "status": "COMPLETED",
  "requestedAmount": 10000,
  "feeAmount": 0,
  "commissionAmount": 0,
  "netAmountToDestination": 10000,
  "currency": "KMF",
  "completedAt": "Instant",
  "replayed": false
}
```

### 6.7 `AgentCashOutResponse`

```json
{
  "transactionId": "uuid|null",
  "status": "COMPLETED|PENDING_APPROVAL|APPROVED|REJECTED|EXPIRED",
  "approvalId": "uuid|null",
  "requestedAmount": 25000,
  "feeAmount": 0,
  "commissionAmount": 0,
  "netAmountToDestination": 25000,
  "currency": "KMF",
  "completedAt": "Instant|null",
  "replayed": false
}
```

### 6.8 `AgentCardSaleResponse`

```json
{
  "transactionId": "uuid",
  "status": "COMPLETED",
  "cardId": "uuid",
  "customerId": "uuid",
  "cardPrice": 5000,
  "commissionAmount": 0,
  "completedAt": "Instant",
  "replayed": false
}
```

### 6.9 `AgentCardReplacementResponse`

```json
{
  "transactionId": "uuid|null",
  "status": "COMPLETED|null",
  "newCardId": "uuid",
  "oldCardId": "uuid",
  "customerId": "uuid",
  "replacementFee": 5000,
  "commissionAmount": 0,
  "completedAt": "Instant|null",
  "replayed": false
}
```

`transactionId`, `status`, `replacementFee`, `commissionAmount` et `completedAt` peuvent etre `null` dans le cas "already replaced" retourne sans nouvelle transaction.

### 6.10 `CustomerLookupResponse`

```json
{
  "customerId": "uuid",
  "fullName": "Customer Name",
  "phoneCountryCode": "269",
  "phoneNumber": "3210000",
  "status": "ACTIVE",
  "kycLevel": "KYC_BASIC"
}
```

### 6.11 `EnrollCustomerResponse`

```json
{
  "customerId": "uuid",
  "externalRef": "CUST-00001",
  "walletId": "uuid"
}
```

### 6.12 `KycDocumentResponse`

```json
{
  "id": "uuid",
  "ownerActorType": "CUSTOMER",
  "ownerActorId": "uuid",
  "documentType": "NATIONAL_ID",
  "contentHash": "sha256-hex",
  "uploadedByActorType": "AGENT",
  "uploadedByActorId": "uuid",
  "uploadedAt": "Instant",
  "status": "PENDING_REVIEW"
}
```

### 6.13 `AgentCardStockView`

```json
{
  "id": "uuid",
  "nfcUid": "04AABBCCDDEEFF",
  "internalCardNumber": "CARD-00001",
  "batchRef": "BATCH-001",
  "producedAt": "2026-01-01",
  "assignedAt": "Instant",
  "status": "ASSIGNED_TO_AGENT"
}
```

### 6.14 `CardResponse`

```json
{
  "id": "uuid",
  "nfcUid": "04AABBCCDDEEFF",
  "internalCardNumber": "CARD-00001",
  "walletId": "uuid",
  "customerId": "uuid",
  "cardType": "STANDARD|PREMIUM|CORPORATE",
  "status": "ACTIVE|BLOCKED|LOST|STOLEN|EXPIRED|CLOSED|ISSUED",
  "pinEnabled": false,
  "issuedByAgentId": "uuid|null",
  "issuedAt": "Instant",
  "activatedAt": "Instant|null",
  "expiresAt": "2031-05-04",
  "lastUsedAt": "Instant|null",
  "lastUsedTerminalId": "uuid|null",
  "replacedByCardId": "uuid|null",
  "replacementOfCardId": "uuid|null"
}
```

### 6.15 Enumerations utiles

| Enum | Valeurs |
|---|---|
| `AgentStatus` | `PENDING_KYC`, `ACTIVE`, `SUSPENDED`, `CLOSED` |
| `CustomerStatus` | `PENDING_KYC`, `ACTIVE`, `SUSPENDED`, `FROZEN`, `CLOSED` |
| `MerchantStatus` | `PENDING_KYC`, `ACTIVE`, `SUSPENDED`, `FROZEN`, `CLOSED` |
| `KycLevel` | `KYC_NONE`, `KYC_BASIC`, `KYC_VERIFIED`, `KYC_ENHANCED` |
| `WalletStatus` | `ACTIVE`, `FROZEN`, `SUSPENDED`, `CLOSED` |
| `TransactionStatus` | `PENDING`, `AUTHORIZED`, `COMPLETED`, `DECLINED`, `EXPIRED`, `REVERSED` |
| `TransactionType` utile agent | `CASH_IN`, `CASH_OUT`, `CARD_SALE`, `CARD_REPLACEMENT`, `COMMISSION_PAYOUT`, `FEE_COLLECTION`, `REVERSAL`, `AGENT_FUND_IN`, `AGENT_FUND_OUT` |
| `CardStockStatus` | `IN_WAREHOUSE`, `ASSIGNED_TO_AGENT`, `SOLD`, `RETURNED`, `SPOILED` |
| `CardStatus` | `ISSUED`, `ACTIVE`, `BLOCKED`, `LOST`, `STOLEN`, `EXPIRED`, `CLOSED` |
| `KycDocumentType` | `NATIONAL_ID`, `PASSPORT`, `PROOF_OF_ADDRESS`, `BUSINESS_LICENSE`, `OTHER` |
| `KycDocumentStatus` | `PENDING_REVIEW`, `ACCEPTED`, `REJECTED` |
| `PayoutStatus` | `PENDING`, `PAID`, `FAILED`, `CANCELLED` |
| `SettlementMode` | `IMMEDIATE`, `BATCH_DAILY`, `BATCH_WEEKLY` |
| `ApprovalStatus` | `PENDING_APPROVAL`, `APPROVED`, `REJECTED`, `EXPIRED` |

---

## 7. Regles metier et effets financiers

### 7.1 Cash-in

Flux comptable courant :

- `DEBIT` wallet agent : montant demande ajuste par le porteur de frais ;
- `CREDIT` wallet client : montant net destination ;
- `CREDIT` wallet `SYSTEM_REVENUE` : frais si non zero.

La transaction est creee `PENDING`, autorisee, postee en ledger double-entry, puis `COMPLETED`.

### 7.2 Cash-out

Flux comptable courant en execution :

- `DEBIT` wallet marchand : montant demande ajuste par frais ;
- `CREDIT` wallet agent : montant net destination ;
- `CREDIT` wallet `SYSTEM_REVENUE` : frais si non zero.

Si le montant declenche `LARGE_CASH_OUT`, le backend cree une approval request backoffice. L'execution effective utilise ensuite une cle idempotence deterministe `LARGE-CASH-OUT-APPROVAL-{approvalId}`.

### 7.3 Card sale

Flux :

- l'agent remet le prix de carte a la plateforme via debit wallet agent ;
- `SYSTEM_REVENUE` est credite du `cardPrice`;
- la carte est creee depuis `CardStock`, liee au client, activee ;
- le stock est marque `SOLD`.

### 7.4 Card replacement

Flux :

- identique a `CARD_SALE` sur le plan financier ;
- ancienne carte : `replacedByCardId` renseigne puis statut `CLOSED`;
- nouvelle carte : `replacementOfCardId` pointe vers l'ancienne, carte activee et liee au meme customer/wallet ;
- stock consomme et marque `SOLD`.

### 7.5 Commissions

Les transactions agent peuvent produire une `CommissionPayout`.

- Si `SettlementMode.IMMEDIATE`, le payout peut etre poste immediatement sur le wallet agent.
- Si batch, il reste `PENDING` jusqu'au settlement job.
- L'historique est consultable par `GET /api/v1/agent/commissions`.

---

## 8. Erreurs, securite et idempotence

### 8.1 Erreurs communes

| HTTP | Code typique | Cas |
|---:|---|---|
| 400 | `VALIDATION_FIELD_REQUIRED`, `VALIDATION_INVALID_FORMAT`, `VALIDATION_ERROR` | Body invalide, param invalide, header requis absent |
| 401 | `UNAUTHORIZED`, `INVALID_TOKEN`, `TOKEN_EXPIRED`, `TOKEN_REVOKED`, `MFA_INVALID` | Session absente/invalide, challenge OTP invalide |
| 403 | `FORBIDDEN` | JWT non-agent ou ressource hors scope |
| 404 | `CUSTOMER_NOT_FOUND`, `MERCHANT_NOT_FOUND`, `AGENT_NOT_FOUND`, `WALLET_NOT_FOUND`, `CARD_NOT_FOUND`, `CARD_STOCK_NOT_FOUND` | Ressource manquante |
| 409 | `DUPLICATE_IDEMPOTENCY_KEY`, `CONFLICT` | Course concurrente ou conflit unique |
| 422 | `ACTOR_SUSPENDED`, `ACTOR_CLOSED`, `INSUFFICIENT_BALANCE`, `CASH_OUT_NOT_ALLOWED`, `CARD_STOCK_WRONG_STATUS` | Regle metier |
| 429 | `TERMINAL_RATE_LIMIT` | Rate limit sur `/api/v1/auth/**` |
| 500 | `INTERNAL_ERROR` | Erreur inattendue |

### 8.2 Rate limiting

Tous les endpoints `/api/v1/auth/**`, donc les endpoints auth agent, sont limites par IP.

Valeur par defaut :

- `authMaxRequestsPerMinute = 10`

Le code erreur actuel pour un 429 est `TERMINAL_RATE_LIMIT`, meme pour les routes auth.

### 8.3 Idempotence

Endpoints exigeant `Idempotency-Key` :

- `POST /api/v1/agent/cash-in`
- `POST /api/v1/agent/cash-out`
- `POST /api/v1/agent/card-sell`
- `POST /api/v1/agent/customers/{customerId}/cards/{cardId}/replace`

Comportement backend confirme :

- si la cle existe deja, le backend retourne la transaction precedente avec `replayed=true` lorsque l'operation correspond a une transaction existante ;
- le backend actuel ne compare pas explicitement le payload original et le nouveau payload sur replay ;
- si deux requetes concurrentes reservent la meme cle, la contrainte unique peut produire `DUPLICATE_IDEMPOTENCY_KEY` ;
- pour `CARD_REPLACEMENT`, il existe aussi une idempotence metier : si l'ancienne carte a deja ete remplacee, la carte de remplacement existante est retournee sans nouvelle transaction, meme avec une cle differente.

Regle frontend :

- generer une cle UUID v4 par tentative utilisateur ;
- conserver la meme cle pour un retry reseau de la meme operation ;
- generer une nouvelle cle si l'utilisateur modifie un champ metier.

### 8.4 Correlation ID

| Endpoint | Si `X-Correlation-Id` absent | Si invalide |
|---|---|---|
| `cash-in`, `cash-out`, `card-sell` | UUID genere | `400 VALIDATION_ERROR` |
| `card-replace` | `null` | ignore, transmis `null` |
| `customers/enroll` | UUID genere | UUID genere |
| autres endpoints agent | non utilise ou audit sans correlation |

### 8.5 Scope et confidentialite

- Les endpoints lecture sont scopes a `principal.actorId()`.
- Un agent ne peut pas lire le stock d'un autre agent.
- Un agent ne peut pas lire une transaction qui n'implique pas son wallet.
- Lookup client expose seulement les champs necessaires a l'identification.
- Les documents KYC ne retournent pas le fichier, seulement la metadata et le hash.

---

## 9. Fonctionnalites absentes cote API Agent

Ne pas construire d'ecran agent qui depend de ces endpoints, car ils n'existent pas dans la surface agent actuelle :

| Fonctionnalite | Statut API Agent |
|---|---|
| Liste globale des clients | Non exposee |
| Detail complet client par `customerId` | Non expose |
| Liste des cartes d'un client | Non exposee cote agent |
| Recherche marchand par telephone/ref | Non exposee |
| Annulation/reversal initie par agent | Non expose |
| Modification profil agent | Non exposee |
| Changement telephone agent | Non expose |
| PIN carte client ou reset PIN par agent | Non expose |
| Telechargement du fichier KYC apres upload | Non expose |
| Decision KYC (`ACCEPTED`/`REJECTED`) par agent | Non exposee |
| Consultation d'une approval `LARGE_CASH_OUT` par l'agent | Non exposee |
| Historique audit/activity agent | Non expose |

---

## 10. Index des preuves code

| Domaine | Classe(s) |
|---|---|
| Auth agent OTP/refresh/logout | `security.api.AgentAuthController`, `security.application.AgentAuthenticationService` |
| TOTP agent | `security.api.AgentTotpController`, `security.application.TotpEnrollmentService` |
| JWT/security | `security.infrastructure.JwtService`, `security.infrastructure.JwtAuthenticationFilter`, `shared.infrastructure.config.SecurityConfig` |
| Profil/solde agent | `agentportal.api.AgentMeController`, `agentportal.application.AgentReadService` |
| Resume quotidien | `agentportal.api.AgentSummaryController`, `agentportal.application.AgentReadService` |
| Releve agent | `agentportal.api.AgentStatementController` |
| Transactions agent lecture | `agentportal.api.AgentTransactionListController` |
| Commissions agent | `agentportal.api.AgentCommissionController` |
| Limites agent | `agentportal.api.AgentLimitsController` |
| Stock cartes agent | `card.api.AgentCardStockController` |
| Lookup, cash-in, cash-out, card-sale | `transaction.api.AgentTransactionController` |
| Cash-in | `transaction.application.CashInUseCase` |
| Cash-out + approval threshold | `transaction.application.CashOutSubmissionUseCase`, `transaction.application.CashOutUseCase`, `backoffice.application.ApprovalService` |
| Card sale | `transaction.application.CardSaleUseCase` |
| Enrolement client | `identity.api.AgentEnrollmentController`, `identity.application.EnrollCustomerUseCase` |
| KYC documents | `kyc.api.AgentKycController`, `kyc.application.StoreKycDocumentUseCase` |
| Report lost/stolen | `card.api.AgentCardController`, `card.application.AgentCardService` |
| Card replacement | `transaction.api.AgentCardReplacementController`, `transaction.application.CardReplacementUseCase` |
| Pagination | `shared.infrastructure.web.CursorUtils`, `shared.infrastructure.web.PagedResponse` |
| Enveloppes API/erreurs | `shared.infrastructure.web.ApiResponse`, `shared.infrastructure.web.ApiError`, `shared.infrastructure.exception.GlobalExceptionHandler` |

---

*Fin du document. Toute la surface listee est derivee des controllers, DTOs et use cases du backend KomoPay au 2026-05-04.*
