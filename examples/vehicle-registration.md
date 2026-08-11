# Example: Vehicle Registration

Submits a vehicle registration (Kfz-Zulassung) via `POST /vehicleRegistrations/registrations`.

> Requires dropshipping API 2.4.0, which is not the SDK default — set
> `DROPSHIPPING_API_VERSION=2.4.0` or pass `apiVersion: '2.4.0'` to `DropshippingConfig`, and make
> sure your client is enabled for that version.

## What it does

1. Builds the registration customization: assignment strategy, service type code, vehicle data, bank details.
2. Sends the request and receives the order ID — that is the entire response.
3. Waits for the webhooks: the customer has to identify themselves and sign the documents, and the URL for each step is delivered by its `…_INITIALIZED` event. The registration is only processed once the customer has completed both.

## The three assignment strategies

The strategy is an object, not a plain enum value — it serializes with a `strategyType`
discriminator and carries the plate data belonging to that strategy:

| Factory | `strategyType` | Meaning | Carries |
|---------|----------------|---------|---------|
| `DS::randomLicensePlateNumber(...)` | `RANDOM` | The registration office assigns an arbitrary available number | `licensePlateType` + optional season months |
| `DS::reservedLicensePlateNumber(...)` | `RESERVATION` | A previously reserved number is used | the plate number, `licensePlateType`, `reservationPin` + optional season months |
| `DS::retainedLicensePlateNumber()` | `RETAINMENT` | The number of the previous plate is kept | — (the number comes from the required `previousLicensePlate` on the customization) |

`RANDOM` leaves only the *number* to the office -- the plate type is still yours to choose.
`RETAINMENT` carries nothing, since it keeps the previous plate as-is.

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
| `VehicleRegistrationLicensePlateNumberAssignmentStrategyInterface` | Implemented by the `…Random`, `…Reservation` and `…Retained` strategies, each carrying its own plate fields |
| `VehicleRegistrationPreviousLicensePlate` | The plate the vehicle carried before, if any |
| `VehicleRegistrationVehicleHolder` | Vehicle holder address and birth details |
| `VehicleRegistrationResponse` | `orderId` |
| `VehicleRegistrationLicensePlateNumberAssignmentStrategyType` | Enum: `RANDOM`, `RESERVATION`, `RETAINMENT` |
| `VehicleRegistrationServiceTypeCode` | Enum: `NZ`, `WZ`, `UO`, `UI`, `UM`, `WG`, `UG`, `HA` |
| `VehicleRegistrationVehicleType` | Enum: `CAR`, `MOTORCYCLE`, `TRAILER` |
| `VehicleRegistrationLicensePlateType` | Enum: `REGULAR`, `ELECTRIC`, `HISTORICAL`, `*_SEASON` variants |
| `VehicleRegistrationXkfzEvent` | Webhook event: registration office verdict, files, costs, assigned plate |
| `VehicleRegistrationXkfzEventLicensePlate` | The plate assigned to the vehicle |
| `VehicleRegistrationIdentityVerificationInitializedEvent` | Webhook event: identity check started, carries `identityVerificationUrl` |
| `VehicleRegistrationDocumentSignatureInitializedEvent` | Webhook event: signing started, carries `documentSignatureUrl` |
| `VehicleRegistrationXkfzEventStatus` | Enum: `ACCEPTED`, `APPROVED`, `PROCESSED`, `REJECTED`, `FAILED`, … |
| `VehicleRegistrationXkfzEventFilePurposeType` | Enum: what an attached document is — approval notice, provisional certificate, … |

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

## What each service type code requires

`NZ` (Neuzulassung) is the first registration of a vehicle, so there is nothing that came before
it. Every other code continues a registration the vehicle already had, and needs it identified.
`VehicleRegistrationCustomization` throws on both sides of that line:

| Field | `NZ` | `WZ`, `WG`, `UG`, `UM`, `UO`, `UI`, `HA` |
|-------|------|------------------------------------------|
| `vehicleRegistrationCertificateSecurityCode` (ZB I) | must be `null` | required |
| `previousLicensePlate` | must be `null` | required |
| `deregistered` | must be `true` | `true` for `WZ` and `WG`, unconstrained otherwise |

The API rejects a ZB I code on an `NZ` with `verificationCode must be null` — `verificationCode`
is its internal name for the field the spec calls `vehicleRegistrationCertificateSecurityCode`,
and a ZB I only exists once this registration has issued it. That also rules out
`DS::retainedLicensePlateNumber()` for `NZ`: RETAINMENT has no number to keep without a previous
plate, so it insists on one.

Two more rules apply to the plate the vehicle keeps under RETAINMENT. The plates stay screwed
on and their seals unbroken, so nobody can read the codes off them —
`frontLicensePlateSecurityCode` and `rearLicensePlateSecurityCode` on `previousLicensePlate`
must stay `null` there. The other two strategies take them.

None of these rules is published in any spec up to 2.4.0, where
`VehicleRegistrationServiceTypeCode` is a bare enum without descriptions and the fields are
declared plainly nullable. They are enforced here because the API only rejects them *after*
identification and QES have run.

## Webhooks

Everything after the order creation is announced via webhook events (webhooks spec 3.2.0), all
typed:

| Event type | Class |
|------------|-------|
| `VEHICLE_REGISTRATION_IDENTITY_VERIFICATION_INITIALIZED` | `VehicleRegistrationIdentityVerificationInitializedEvent` |
| `VEHICLE_REGISTRATION_IDENTITY_VERIFICATION_SUCCEEDED` | `VehicleRegistrationIdentityVerificationSucceededEvent` |
| `VEHICLE_REGISTRATION_IDENTITY_VERIFICATION_FAILED` | `VehicleRegistrationIdentityVerificationFailedEvent` |
| `VEHICLE_REGISTRATION_DOCUMENT_SIGNATURE_INITIALIZED` | `VehicleRegistrationDocumentSignatureInitializedEvent` |
| `VEHICLE_REGISTRATION_DOCUMENT_SIGNATURE_SUCCEEDED` | `VehicleRegistrationDocumentSignatureSucceededEvent` |
| `VEHICLE_REGISTRATION_DOCUMENT_SIGNATURE_FAILED` | `VehicleRegistrationDocumentSignatureFailedEvent` |
| `VEHICLE_REGISTRATION_XKFZ_EVENT` | `VehicleRegistrationXkfzEvent` |

Register a handler per event type as in [webhooks.md](webhooks.md). The XKFZ event's `files`
carry a `fileAccessKey` — pass it to
`$client->vehicleRegistrations->downloadFileContent($file->fileAccessKey)` to fetch the content
before the file's `expirationTime` passes.

## Run

```bash
php examples/vehicle-registration.php
```

Replace the credentials, vehicle data and `gksConfigurationId` with your own before running.
