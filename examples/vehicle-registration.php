<?php

declare(strict_types=1);

require_once __DIR__ . '/_bootstrap.php';

use Dropshipping\DS;
use Dropshipping\Enums\{
    Gender,
    VehicleRegistrationLicensePlateType,
    VehicleRegistrationServiceTypeCode,
    VehicleRegistrationVehicleType,
};

// NOTE: Vehicle registration requires dropshipping API 2.4.0, which is not the SDK
// default. Set DROPSHIPPING_API_VERSION=2.4.0 or pass apiVersion to DropshippingConfig.

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
            vehicleTitleSecurityCode: 'ABCDEF123456',                // ZB II security code, exactly 12 chars
            iban: 'DE89370400440532013000',
            bic: 'COBADEFFXXX',
            // ZB I code and previousLicensePlate belong to a vehicle that was registered
            // before. NZ is a first registration, so both must stay null — passing them
            // throws. Every other service type code turns that around and *requires*
            // both. See the class docblock of VehicleRegistrationCustomization.
            vehicleTitleNumber: 'AB123456',                          // optional, exactly 8 chars
        ),
        vehicleHolderAddress: $address,
        vehicleHolderPlaceOfBirth: 'Berlin',   // required
        vehicleHolderBirthDate: '1990-01-31',  // required, ISO 8601
        vehicleHolderBirthName: 'Musterfrau',  // optional
        externalOrderId: 'registration-001',   // optional
        gksConfigurationId: 'your-gks-uuid',   // optional
        contractPartnerKopaKey: 'K123X',       // optional
    ),
);

echo "Registration order created: {$response->orderId}\n";

// That order ID is all the response carries. Everything else — including the two URLs the
// customer MUST visit before anything is processed — arrives asynchronously via webhooks
// (spec 3.2.0), as typed events:
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
