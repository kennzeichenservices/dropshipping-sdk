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

## Industry code

`industryCode` is required and has to be a code from `GET /vehicleHolderIndustries`, see
[vehicle-holder-industries.php](vehicle-holder-industries.php). The SDK checks the shape (three
characters of `A-Z` and `0-9`), only the API knows which codes exist. When reading,
`company->industryCode` is `null` for a record that was stored without one.

## Signer

The signer's `gender` is required. When reading, `signer->gender` is `null` for a record that was
stored without one.

## Delivery configurations

Where the registration documents go, built with `DS::registrationDeliveryConfigurations(...)`. Left
out, both are delivered to the vehicle holder. The spec does not say how this relates to the delivery
configurations of a registration ordered under the power of attorney.

## Expiration date

At most one year from today, `YYYY-MM-DD`. Leave it `null` and the API sets exactly that maximum.
The SDK checks the format only, the upper bound is left to the API because it depends on the
server's clock.

## Not idempotent

Creating twice creates two records. After a timeout, check the listing before trying again.

## Ordering under it

Pass the ID to `DS::registrationLegalPersonVehicleHolderFromDurablePowerOfAttorney(...)` and that
holder to `DS::vehicleRegistrationWithHolder(...)`, see
[vehicle-registration.md](vehicle-registration.md). The API takes name, address, industry and tax
details from the power of attorney, so the registration carries no IBAN. It only accepts one that
is `COMPLETE`, active and not expired, and it does not allow reusing an earlier identification
with it.

## Run

```bash
php examples/durable-powers-of-attorney.php
```

Replace the `DropshippingConfig` values in `_bootstrap.php` and the signer's email address before
running. The create step starts a real signing process.
