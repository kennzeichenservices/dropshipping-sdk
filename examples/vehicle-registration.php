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
