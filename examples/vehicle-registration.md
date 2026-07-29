# Example: Vehicle Registration (Beta)

Submits a vehicle registration (Kfz-Zulassung) via `POST /vehicleRegistrations/registrations`.

> **Beta** — vehicle registration was introduced in dropshipping API 2.3.2. The request and
> response shapes may still change; treat changes to these classes as non-breaking.

## What it does

1. Builds the registration customization: assignment strategy, service type code, vehicle data, bank details.
2. Sends the request and receives an order ID, an identity verification vendor ID, and the **customer input form URL**.
3. Hands the customer that URL — the registration is only processed once they have provided their identification data and signatures there.

## The three assignment strategies

| Strategy | Meaning | Extra fields |
|----------|---------|--------------|
| `RANDOM` | The registration office assigns an arbitrary available number | — |
| `RESERVATION` | A previously reserved number is used | `reservationPin` is **required** (enforced locally) |
| `RETAINMENT` | The number of the previous plate is kept | `previousLicensePlate` |

## Key classes

| Class | Purpose |
|-------|---------|
| `VehicleRegistrationRequest` | Top-level request DTO |
| `VehicleRegistrationCustomization` | Vehicle, plate, insurance and bank data |
| `VehicleRegistrationPreviousLicensePlate` | The plate the vehicle carried before, if any |
| `VehicleRegistrationVehicleHolder` | Vehicle holder address |
| `VehicleRegistrationResponse` | `orderId`, `identityVerificationVendorId`, `customerInputFormUrl` |
| `VehicleRegistrationLicensePlateNumberAssignmentStrategy` | Enum: `RANDOM`, `RESERVATION`, `RETAINMENT` |
| `VehicleRegistrationServiceTypeCode` | Enum: `NZ`, `WZ`, `UO`, `UI`, `UM`, `WG`, `UG`, `HA` |
| `VehicleRegistrationVehicleType` | Enum: `CAR`, `MOTORCYCLE`, `TRAILER` |
| `VehicleRegistrationLicensePlateType` | Enum: `REGULAR`, `ELECTRIC`, `HISTORICAL`, `*_SEASON` variants |

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
