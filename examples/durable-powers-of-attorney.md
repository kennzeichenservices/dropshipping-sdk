# Example: Durable Powers of Attorney

Creates, lists and looks up durable powers of attorney (Dauervollmachten). A durable power of
attorney lets a company authorise vehicle registrations once instead of signing a power of
attorney per order.

> Needs dropshipping API 2.7.0. Set `DROPSHIPPING_API_VERSION=2.7.0` or pass
> `apiVersion: '2.7.0'` to `DropshippingConfig`. Below 2.7.0 the API answers `403 Forbidden`.

## What it does

1. `POST /vehicleRegistrations/durablePowersOfAttorney` with the company, its tax data and the
   signer. The API answers with the new record in status `INCOMPLETE` and mails the signer.
2. `GET /vehicleRegistrations/durablePowersOfAttorney` lists every record of the client, revoked
   and failed ones included.
3. `GET /vehicleRegistrations/durablePowersOfAttorney/{id}` fetches one.

## Lifecycle

| Status | Meaning |
|--------|---------|
| `INCOMPLETE` | Created, documents not generated or not signed yet |
| `COMPLETE` | Generated and signed. Announced by `VEHICLE_REGISTRATION_DURABLE_POWER_OF_ATTORNEY_COMPLETION` |
| `ERROR` | Generation or signature failed. Announced by `VEHICLE_REGISTRATION_DURABLE_POWER_OF_ATTORNEY_ERROR` with a `message` |

Revocation is not a status: `active` turns `false`. `isUsable()` on a record, and `usable()` on
the listing, combine all three: `COMPLETE`, `active` and not past `expirationDate`. The spec does
not say whether the expiration date itself still counts; the SDK treats it as the last valid day.

## Tax data

Exactly one of two variants:

- `DS::durablePowerOfAttorneySepaTaxData($iban, $bic)`: SEPA direct debit mandate. The BIC is only
  required for an IBAN from Monaco, San Marino or Switzerland.
- `DS::durablePowerOfAttorneyCustomsTaxData('123A4567')`: major customer ID of the customs
  administration, replacing the mandate.

## Expiration date

At most one year from today, `YYYY-MM-DD`. Leave it `null` and the API sets exactly that maximum.
The SDK checks the format only, the upper bound is left to the API because it depends on the
server's clock.

## Not idempotent

Creating twice creates two records. After a timeout, check the listing before trying again.

## Not covered yet

API 2.7.0 does not yet take a durable power of attorney as a reference when ordering a vehicle
registration. Until it does, creating one has no effect on orders.

## Run

```bash
php examples/durable-powers-of-attorney.php
```

Replace the `DropshippingConfig` values in `_bootstrap.php` and the signer's email address before
running. The create step starts a real signing process.
