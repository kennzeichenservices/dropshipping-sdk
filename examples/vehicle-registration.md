# Example: Vehicle Registration (Beta)

Submits a vehicle registration (Kfz-Zulassung) via `POST /vehicleRegistrations/registrations`.

> **Beta** — vehicle registration was introduced in dropshipping API 2.3.2. The request and
> response shapes may still change; treat changes to these classes as non-breaking.

## What it does

1. Builds the registration customization: assignment strategy, service type code, vehicle data, bank details.
2. Sends the request and receives an order ID, an identity verification vendor ID, and the **customer input form URL**.
3. Hands the customer that URL — the registration is only processed once they have provided their identification data and signatures there.

## The three assignment strategies

The strategy is an object, not a plain enum value — it serializes with a `strategyType`
discriminator and carries the plate data belonging to that strategy:

| Factory | `strategyType` | Meaning | Carries |
|---------|----------------|---------|---------|
| `DS::randomLicensePlateNumber()` | `RANDOM` | The registration office assigns an arbitrary available number | — |
| `DS::reservedLicensePlateNumber(...)` | `RESERVATION` | A previously reserved number is used | the reserved plate + `reservationPin` |
| `DS::retainedLicensePlateNumber()` | `RETAINMENT` | The number of the previous plate is kept | — (set `previousLicensePlate` on the customization) |

Only the `RESERVATION` strategy takes a plate, so `licensePlateType` and the season months
are only specifiable there. For `RANDOM` and `RETAINMENT` the registration office decides.

```php
licensePlateNumberAssignmentStrategy: DS::reservedLicensePlateNumber(
    plate: DS::plate('B', 'AB', '1234'),
    licensePlateType: VehicleRegistrationLicensePlateType::ElectricSeason,
    reservationPin: '1234',
    seasonStartMonth: 3,  // optional
    seasonEndMonth: 10,   // optional
),
```

## Key classes

| Class | Purpose |
|-------|---------|
| `VehicleRegistrationRequest` | Top-level request DTO |
| `VehicleRegistrationCustomization` | Vehicle, insurance and bank data |
| `VehicleRegistrationLicensePlate` | Plate number components, type and season months |
| `VehicleRegistrationLicensePlateNumberAssignmentStrategyInterface` | Implemented by the `…Random`, `…Reservation` and `…Retained` strategies |
| `VehicleRegistrationPreviousLicensePlate` | The plate the vehicle carried before, if any |
| `VehicleRegistrationVehicleHolder` | Vehicle holder address and birth details |
| `VehicleRegistrationResponse` | `orderId`, `identityVerificationVendorId`, `customerInputFormUrl` |
| `VehicleRegistrationLicensePlateNumberAssignmentStrategyType` | Enum: `RANDOM`, `RESERVATION`, `RETAINMENT` |
| `VehicleRegistrationServiceTypeCode` | Enum: `NZ`, `WZ`, `UO`, `UI`, `UM`, `WG`, `UG`, `HA` |
| `VehicleRegistrationVehicleType` | Enum: `CAR`, `MOTORCYCLE`, `TRAILER` |
| `VehicleRegistrationLicensePlateType` | Enum: `REGULAR`, `ELECTRIC`, `HISTORICAL`, `*_SEASON` variants |

## Vehicle holder

The registration API requires more than the address: both `placeOfBirth` and `birthDate`
(ISO 8601) are mandatory. `birthName` is optional.

## Field lengths enforced by the SDK

The API constrains several fields to exact lengths; the DTO constructors reject violations
before any HTTP call:

| Field | Length |
|-------|--------|
| `electronicInsuranceConfirmationNumber` (eVB) | exactly 7 |
| `vehicleIdentificationNumber` (VIN / FIN) | 4–17 |
| `vehicleRegistrationCertificateSecurityCode` (ZB I) | exactly 7 |
| `vehicleTitleNumber` (Fahrzeugbriefnummer) | exactly 8 |
| `vehicleTitleSecurityCode` (ZB II) | exactly 12 |
| `iban` | 15–34 |
| `bic` | 8–11 |
| `reservationPin` | 4–12 |
| `placeOfBirth` | 1–150 |
| `birthName` | 1–100 |
| front/rear plate security codes on `previousLicensePlate` | exactly 3 |

## Webhooks

Registration results are announced via webhook events. Those events are **not yet described
in any published webhooks spec**, so the SDK has no typed classes for them. Build the pipeline
with unknown-event tolerance to receive them as `UnknownWebhookEvent` (carrying `rawEventType`
and the full `payload`) instead of an exception:

```php
$pipeline = DS::webhookPipeline($secret, tolerateUnknownEvents: true);
```

## Run

```bash
php examples/vehicle-registration.php
```

Replace the credentials, vehicle data and `gksConfigurationId` with your own before running.
