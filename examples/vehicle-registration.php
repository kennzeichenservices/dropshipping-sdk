<?php

declare(strict_types=1);

require_once __DIR__ . '/_bootstrap.php';

use Dropshipping\DS;
use Dropshipping\Enums\{
    Gender,
    IdentityVerificationDocumentType,
    VehicleRegistrationLicensePlateType,
    VehicleRegistrationServiceTypeCode,
    VehicleRegistrationVehicleType,
};

// NOTE: Vehicle registration works on the SDK default, dropshipping API 2.4.0, except for a
// Halteränderung (HA), which needs 2.5.0 — set
// DROPSHIPPING_API_VERSION=2.5.0 or pass apiVersion to DropshippingConfig.
//
// On 2.6.0 the identityVerificationDocumentType below stops being optional: the API rejects a
// registration without it. 2.4.0 and 2.5.0 do not know the field, and the SDK leaves it out of
// the payload while it is null, so one script can serve all three versions as long as the value
// is set whenever DROPSHIPPING_API_VERSION is 2.6.0 or later.
//
// 2.7.0 goes one step further: vehicleHolder.type becomes required, and identityVerificationDocumentType,
// iban and bic move onto the holder. DS::vehicleRegistration() below keeps sending the 2.6.0 shape,
// which 2.7.0 rejects with 400. On 2.7.0 use DS::vehicleRegistrationWithHolder() with an explicit
// holder instead, as shown at the end of this script.

$address = DS::address(
    firstName: 'Max',
    lastName: 'Mustermann',
    gender: Gender::Male,
    streetName: 'Musterstraße',
    houseNumber: '1',
    zipCode: '12345',
    cityName: 'Berlin',
    countryCode: 'DE',
);

// Optional recipient for a deviating delivery address of the registration documents.
// A private individual instead of a company: DS::registrationDocumentPerson(
//     address: ..., firstName: 'Erika', lastName: 'Mustermann',
//     gender: Gender::Female, birthDate: '1985-07-14',
// )
$dealer = DS::registrationDocumentCompany(
    // Street, house number, postal code and city only — the office takes the name from the
    // recipient and delivers within Germany, so this is not the full DS::address(...).
    address: DS::registrationDeliveryAddress(
        streetName: 'Autohausstraße',
        houseNumber: '7a',
        zipCode: '80331',
        cityName: 'München',
    ),
    name: 'Autohaus Muster GmbH',
);

$response = $client->vehicleRegistrations->createRegistration(
    DS::vehicleRegistration(
        email: 'max@example.com',
        customization: DS::registrationCustomization(
            // The office picks an arbitrary available number of the given plate type.
            // Alternatives:
            //   DS::reservedLicensePlateNumber(...) — use a previously reserved number
            //   DS::retainedLicensePlateNumber()    — keep the number of previousLicensePlate
            licensePlateNumberAssignmentStrategy: DS::randomLicensePlateNumber(
                licensePlateType: VehicleRegistrationLicensePlateType::Regular,
                // seasonStartMonth: 4, seasonEndMonth: 10,  // for *_SEASON plate types
            ),
            vehicleRegistrationServiceTypeCode: VehicleRegistrationServiceTypeCode::NZ, // Neuzulassung
            deregistered: true,                                      // must be true for NZ, WZ and WG
            vehicleType: VehicleRegistrationVehicleType::Car,
            electronicInsuranceConfirmationNumber: 'ABC1234',        // eVB-Nummer, exactly 7 chars
            vehicleIdentificationNumber: 'WBA12345678901234',        // VIN / FIN
            vehicleTitleSecurityCode: 'B12345678900',                // ZB II security code, exactly 12 chars
            iban: 'DE89370400440532013000',
            // Only required for an iban from Monaco (MC), San Marino (SM) or Switzerland (CH)
            // since 2.7.0, optional for every other country.
            bic: 'COBADEFFXXX',
            // ZB I code and previousLicensePlate belong to a vehicle that was registered
            // before. NZ is a first registration, so both must stay null — passing them
            // throws. Every other service type code turns that around and *requires*
            // both. See the class docblock of VehicleRegistrationCustomization.
            vehicleTitleNumber: 'AB123456',                          // optional, exactly 8 chars
            // Optional: where the two registration documents go once the office has issued
            // them. Omitted, both are posted to the vehicle holder. Passed, both have to be
            // configured — so the one that keeps the default says so explicitly.
            deliveryConfigurations: DS::registrationDeliveryConfigurations(
                // ZB I (Fahrzeugschein) stays with the holder.
                vehicleRegistrationCertificate: DS::deliverToVehicleHolder(),
                // ZB II (Fahrzeugbrief) goes to the dealership instead.
                // Alternative: DS::pickupByThirdParty($dealer) — the office keeps the
                // document and the third party collects it there.
                vehicleTitle: DS::deliverToThirdParty($dealer),
            ),
        ),
        vehicleHolderAddress: $address,
        vehicleHolderPlaceOfBirth: 'Berlin',   // required
        vehicleHolderBirthDate: '1990-01-31',  // required, ISO 8601
        vehicleHolderBirthName: 'Musterfrau',  // optional
        externalOrderId: 'registration-001',   // optional
        gksConfigurationId: 'your-gks-uuid',   // optional
        contractPartnerKopaKey: 'K123X',       // optional
        // The document the customer identifies themselves with. Required on API 2.6.0,
        // unknown to 2.4.0 and 2.5.0. GERMAN_PASSPORT_WITH_PROOF_OF_ADDRESS and
        // FOREIGN_PAPERS_WITH_PROOF_OF_ADDRESS need a second document showing the address,
        // because neither paper carries a German one.
        identityVerificationDocumentType: IdentityVerificationDocumentType::GermanId,
    ),
);

echo "Registration order created: {$response->orderId}\n";

// That order ID is all the response carries. Everything else — including the two URLs the
// customer MUST visit before anything is processed — arrives asynchronously via webhooks
// (spec 3.3.0), as typed events:
//
//   VEHICLE_REGISTRATION_IDENTITY_VERIFICATION_INITIALIZED  — send the customer to
//                                                             $event->identityVerificationUrl
//   VEHICLE_REGISTRATION_IDENTITY_VERIFICATION_SUCCEEDED / _FAILED
//   VEHICLE_REGISTRATION_DOCUMENT_SIGNATURE_INITIALIZED     — send the customer to
//                                                             $event->documentSignatureUrl
//   VEHICLE_REGISTRATION_DOCUMENT_SIGNATURE_SUCCEEDED / _FAILED
//                                                           — _SUCCEEDED carries the signed
//                                                             documents in $event->applicationFiles
//   VEHICLE_REGISTRATION_XKFZ_EVENT  — the registration office verdict and the assigned plate
//
// Both kinds of file carry a fileAccessKey, but each has its own download operation:
//
//   $client->vehicleRegistrations->downloadFileContent($file->fileAccessKey)
//       for the files on a VEHICLE_REGISTRATION_XKFZ_EVENT
//   $client->vehicleRegistrations->downloadApplicationFileContent($file->fileAccessKey)
//       for the applicationFiles on a VEHICLE_REGISTRATION_DOCUMENT_SIGNATURE_SUCCEEDED
//
// Fetch either before the file's expirationTime passes.
//
// See webhooks.php for the full event handling setup.

// Picked the wrong document type, or the customer turns out not to hold a German ID card? On
// API 2.6.0 the choice can be corrected while the order is still early enough:
//
//   $client->vehicleRegistrations->updateIdentityVerificationDocumentType(
//       orderId: $response->orderId,
//       documentType: IdentityVerificationDocumentType::GermanPassportWithProofOfAddress,
//   );
//
// The API answers 202 and acts asynchronously, so a successful call means the change was
// accepted, not that a new link exists yet. Depending on the change, another
// VEHICLE_REGISTRATION_IDENTITY_VERIFICATION_INITIALIZED event follows with a fresh URL.
// An ApiException with status 409 means document signing has already started or the order is
// not eligible for a change; 404 means the order ID is unknown.


// ---------------------------------------------------------------------------
// Reusing an earlier identification (API 2.6.0)
// ---------------------------------------------------------------------------
//
// recycledVehicleRegistrationOrderId points at an earlier registration order whose identity
// verification and signed files this one takes over, so the customer identifies themselves and
// signs only once. It goes out as recycledVehicleRegistration.orderId.
//
//   DS::vehicleRegistration(
//       // ... as above ...
//       recycledVehicleRegistrationOrderId: 12345,
//   );
//
// Not supported for a legal person on 2.7.0, the request throws for that combination. The
// referenced order has to carry the files this registration needs, which are the power of
// attorney, the SEPA direct debit mandate (except for HA) and, for a natural person, the GDPR
// consent declaration (except for HA). None of that is checkable in the SDK, since it is about the
// other order, so the API is what rejects a mismatch. The SDK only rejects an order ID of zero or
// less, which cannot name an order.

// ---------------------------------------------------------------------------
// API 2.7.0: the vehicle holder carries a type, and can be a legal person
// ---------------------------------------------------------------------------
//
// Up to 2.6.0 the holder was always a natural person and sent no type. 2.7.0 requires the
// discriminator and moves the identity document, iban and bic onto the holder, so the SDK ships
// one holder per payload shape and lets the class decide what goes out. Nothing above changes on
// an SDK update. With a 2.7.0 holder the customization takes iban: null and bic: null, and the
// request takes no identityVerificationDocumentType; the request throws otherwise.
//
// A natural person on 2.7.0:
//
//   DS::vehicleRegistrationWithHolder(
//       email: 'max@example.com',
//       customization: $customization,
//       vehicleHolder: DS::registrationNaturalPersonVehicleHolder(
//           address: $address,
//           placeOfBirth: 'Berlin',
//           birthDate: '1990-01-31',
//           identityVerificationDocumentType: IdentityVerificationDocumentType::GermanId,
//           birthName: 'Musterfrau',             // optional
//           iban: 'DE89370400440532013000',      // required for every code but HA
//           bic: 'COBADEFFXXX',                  // only required for an MC, SM or CH iban
//       ),
//   );
//
// A company as the holder, which 2.7.0 introduced and which the service type code HA does not
// accept. Its data goes into personData:
//
//   $industries = $client->vehicleHolderIndustries->getAll();   // cache this, see
//                                                               // vehicle-holder-industries.php
//   DS::vehicleRegistrationWithHolder(
//       email: 'einkauf@autohaus-muster.de',
//       customization: $customization,
//       vehicleHolder: DS::registrationLegalPersonVehicleHolder(
//           // The address of the company. Its firstName, lastName and gender name a contact
//           // person, and its companyName is NOT read as the holder's name: that is what the
//           // name below is for, so a company carrying one on its address repeats it there.
//           address: $address,
//           name: 'Autohaus Muster GmbH',
//           industryCode: 'G01',          // a code from $industries, three chars of A-Z and 0-9
//           companyNumber: 'HRB 123456',  // company register number, for the verification
//           // Either the major customer ID of the customs administration (Zoll-GK-Nr.), which
//           // settles the motor vehicle tax through customs, or an iban for the SEPA direct
//           // debit mandate. Exactly one of the two, the holder throws otherwise.
//           customsMajorCustomerId: '123A4567',
//           iban: null,
//       ),
//   );
//
// Or a durable power of attorney instead of the company's data. The API takes name, address,
// industry and tax details from there, see durable-powers-of-attorney.php. It has to be COMPLETE,
// active and not expired:
//
//   DS::vehicleRegistrationWithHolder(
//       email: 'einkauf@autohaus-muster.de',
//       customization: $customization,
//       vehicleHolder: DS::registrationLegalPersonVehicleHolderFromDurablePowerOfAttorney(
//           '11111111-1111-1111-1111-111111111111',
//       ),
//   );
