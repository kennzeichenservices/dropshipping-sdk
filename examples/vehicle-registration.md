# Example: Vehicle Registration

Submits a vehicle registration (Kfz-Zulassung) via `POST /vehicleRegistrations/registrations`.

> Works on dropshipping API 2.4.0, the SDK default, except for a Halteränderung (`HA`), which needs
> 2.5.0 — set `DROPSHIPPING_API_VERSION=2.5.0` or pass `apiVersion: '2.5.0'` to
> `DropshippingConfig`, and make sure your client is enabled for that version.
>
> On 2.6.0, `identityVerificationDocumentType` is mandatory and the API answers `400` without it.
> See [Identity verification document type](#identity-verification-document-type).
>
> On 2.7.0, `vehicleHolder.type` is mandatory and a legal person becomes possible as the holder.
> See [Vehicle holder](#vehicle-holder).

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
| `VehicleRegistrationVehicleHolderInterface` | Implemented by the three holder variants, which decide whether and how `type` is sent |
| `VehicleRegistrationVehicleHolder` | Natural person without `type`, the payload for API 2.4.0 to 2.6.0 |
| `VehicleRegistrationNaturalPersonVehicleHolder` | Natural person with `type: NATURAL_PERSON`, API 2.7.0 |
| `VehicleRegistrationLegalPersonVehicleHolder` | Legal person with `type: LEGAL_PERSON`, API 2.7.0 |
| `VehicleHolderIndustriesResponse` | The industry codes a legal person holder can use (API 2.7.0) |
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

For a natural person the registration API requires more than the address: both `placeOfBirth` and
`birthDate` (ISO 8601) are mandatory. `birthName` is optional.

### Which holder class to pass

API 2.7.0 added the discriminator `vehicleHolder.type` and made it required. No earlier version
knows the field, so the SDK ships one holder per payload shape and the class decides what goes out:

| Factory | `type` in the payload | API versions |
|---------|----------------------|--------------|
| `DS::vehicleRegistration(...)`, which wraps `VehicleRegistrationVehicleHolder` | not sent | 2.4.0 to 2.6.0 |
| `DS::registrationNaturalPersonVehicleHolder(...)` | `NATURAL_PERSON` | 2.7.0 |
| `DS::registrationLegalPersonVehicleHolder(...)` | `LEGAL_PERSON` | 2.7.0 |

The two new holders are passed to `DS::vehicleRegistrationWithHolder(...)` rather than
`DS::vehicleRegistration(...)`, which builds its holder itself. An existing call site therefore
keeps sending exactly what it sent before the SDK learned about the discriminator.

### A legal person as the holder

Requires 2.7.0, and the service type code `HA` does not accept it: the request throws on that
combination.

```php
$response = $client->vehicleRegistrations->createRegistration(
    DS::vehicleRegistrationWithHolder(
        email: 'einkauf@autohaus-muster.de',
        customization: $customization,
        vehicleHolder: DS::registrationLegalPersonVehicleHolder(
            address: $address,
            name: 'Autohaus Muster GmbH',
            industryCode: 'G01',
            companyNumber: 'HRB 123456',
        ),
        identityVerificationDocumentType: IdentityVerificationDocumentType::GermanId,
    )
);
```

The address is the address *of the company*. Its `firstName`, `lastName` and `gender` name a
contact person, and its `companyName` is **not** read as the holder's name: that is what `name` is
for, so a company carrying one on its address has to repeat it there. The two may differ.

`industryCode` has to be a code the API knows, fetched from
[vehicle-holder-industries.php](vehicle-holder-industries.php). The SDK checks the shape (three
characters of `A-Z` and `0-9`), the API checks existence, and it does so only after the customer
has identified themselves and signed.

### Paying the motor vehicle tax through customs

A company holding a major customer ID of the customs administration (Zoll-GK-Nr.) settles the tax
that way instead of by SEPA direct debit mandate. Pass it as `customsMajorCustomerId`, and leave
`iban` and `bic` null on the customization; the request throws if both are given, since the API
rejects that combination.

The ID is three digits, an upper case letter and four digits, for instance `123A4567`.

## Identity verification document type

The customer identifies themselves with one of four documents, picked when the order is created
and independent of the verification vendor the API assigns:

| `IdentityVerificationDocumentType` | Document | Needs a proof of address |
|------------------------------------|----------|--------------------------|
| `GermanId` | Personalausweis | no |
| `ResidencePermit` | Aufenthaltstitel | no |
| `GermanPassportWithProofOfAddress` | Reisepass | yes |
| `ForeignPapersWithProofOfAddress` | foreign identity papers | yes |

The last two need a second document showing the address, because neither paper carries a German
one.

The field is **required on API 2.6.0** and unknown to 2.4.0 and 2.5.0. The SDK keeps it optional
and leaves it out of the payload while it is `null`, so one call site serves all three versions.
Set it whenever you run on 2.6.0 or the API answers `400`.

The choice can be corrected afterwards, for instance when the customer turns out not to hold a
German ID card:

```php
$client->vehicleRegistrations->updateIdentityVerificationDocumentType(
    orderId: $response->orderId,
    documentType: IdentityVerificationDocumentType::GermanPassportWithProofOfAddress,
);
```

That operation needs 2.6.0 as well. The API answers `202` and acts asynchronously, so a
successful call means the change was accepted, not that a new link exists yet. Depending on the
change, another `VEHICLE_REGISTRATION_IDENTITY_VERIFICATION_INITIALIZED` event follows with a
fresh URL. An `ApiException` with status `409` means document signing has already started or the
order is not eligible for a change, and `404` means the order ID is unknown.

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
| `name` of a legal person holder | 1–255 |
| `industryCode` of a legal person holder | exactly 3, `A-Z` and `0-9` |
| `companyNumber` of a legal person holder | 1–100, not blank |
| `customsMajorCustomerId` of a legal person holder | exactly 8, `999A9999` |
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

`VehicleRegistrationServiceTypeCode` is a bare enum without descriptions in the spec, and its
field descriptions only name the `NZ` side; the rest of the table is known from API rejections.
The rules are enforced here because the API only rejects them *after* identification and QES
have run.

## Halteränderung (`HA`)

A Halteränderung only changes the holder: the vehicle stays registered, keeps its plate, and the
Zulassungsbescheinigung Teil II is not involved. Spec 2.5.0 publishes what follows from that, and
`VehicleRegistrationCustomization` throws on any violation:

| Field | `HA` | every other code |
|-------|------|------------------|
| `deregistered` | must be `false` | see above |
| `licensePlateNumberAssignmentStrategy` | must be `RETAINMENT` | free (but not `RETAINMENT` for `NZ`) |
| `vehicleTitleSecurityCode`, `vehicleTitleNumber` (ZB II) | must be `null` | security code required |
| `electronicInsuranceConfirmationNumber` | optional | required |
| `iban` | optional | required, unless the holder pays the tax through customs |
| vehicle holder | natural person only | natural or legal person (2.7.0) |

The ZB I security code and `previousLicensePlate` stay required, as for every code but `NZ`. As
`RETAINMENT` keeps the plate on the vehicle, its two security codes stay `null`. Leaving out eVB,
IBAN and BIC is new in 2.5.0, and so is the ban on the ZB II security code — 2.4.0 requires both
for every code. An `HA` built by the SDK is therefore rejected by 2.4.0: use 2.5.0 for it.

## Reusing an earlier identification

`recycledVehicleRegistrationOrderId` points at an earlier registration order whose identity
verification and signed files this one takes over, so the customer identifies themselves and signs
only once. It is serialized as `recycledVehicleRegistration.orderId` and needs API 2.6.0; the field
has been in the spec since then, but 2.7.0 is the first version to describe it.

The referenced order has to match this one:

- the same `vehicleHolder.type`, and for a legal person the same `vehicleHolder.name`, compared case
  insensitively and ignoring surrounding whitespace;
- the files this registration needs: the power of attorney, the SEPA direct debit mandate (except
  for `HA` and for a holder with a `customsMajorCustomerId`) and, for a natural person, the GDPR
  consent declaration (except for `HA`).

None of that is checkable in the SDK, since it is all about the other order, so the API is what
rejects a mismatch. The SDK only rejects an order ID of zero or less, which cannot name an order.

## The BIC and its three countries

Up to 2.6.0 the spec called `bic` required for every service type code but `HA`, and the SDK
enforced that. 2.7.0 narrows it down: only an `iban` from Monaco (`MC`), San Marino (`SM`) or
Switzerland (`CH`) needs a `bic` alongside it, every other country does not. The SDK follows
2.7.0, so a German IBAN without a BIC now goes out rather than being rejected locally. The country
is read from the first two characters of the IBAN, upper cased first, so a lower case IBAN is
judged the same way.

## Webhooks

Everything after the order creation is announced via webhook events (webhooks spec 3.3.0), all
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

Register a handler per event type as in [webhooks.md](webhooks.md).

The failed identity verification carries an optional `message`. For a legal person the provider
checks the company against the register and the named person against its representatives before
any identification link exists. Where that check refuses the order, `message` is one of the keys
of `VehicleRegistrationIdentityVerificationFailureReason`, and `failureReason()` resolves it:

```php
use Dropshipping\Enums\VehicleRegistrationIdentityVerificationFailureReason as FailureReason;

$hint = match ($event->failureReason()) {
    FailureReason::CompanyNotFound,
    FailureReason::CompanyNotVerified,
    FailureReason::CompanyNotActive => 'The company could not be confirmed in the register.',
    FailureReason::CompanyNameMismatch,
    FailureReason::CompanyNumberMismatch => 'Company name or register number differ from the order.',
    FailureReason::MissingHolderName => 'First or last name of the person to identify is missing.',
    FailureReason::NoMatchingRepresentative,
    FailureReason::AmbiguousRepresentative => 'The named person could not be matched to one representative.',
    FailureReason::MissingSoleSigningAuthority => 'The representative may not sign for the company alone.',
    FailureReason::CompanyRepresentativeVerificationFailed => 'The company check failed without a specific reason.',
    null => $event->message ?? 'The identity verification failed.',
};
```

`failureReason()` returns `null` for an event without a message and for any other text, a free
text of the identification vendor for example, or a key a later API version adds. The raw string
stays readable in `$event->message`, so keep the `null` arm.

Two of those events carry files, and each has its own download operation. The XKFZ event's
`files` — the documents the registration office issues — go to
`$client->vehicleRegistrations->downloadFileContent($file->fileAccessKey)`. The document
signature success event's `applicationFiles` — the power of attorney, the GDPR consent
declaration and the motor vehicle tax SEPA mandate, as the customer just signed them — go to
`$client->vehicleRegistrations->downloadApplicationFileContent($file->fileAccessKey)`. The two
key namespaces are not interchangeable. Fetch either before the file's `expirationTime` passes.

## Run

```bash
php examples/vehicle-registration.php
```

Replace the credentials, vehicle data and `gksConfigurationId` with your own before running.
