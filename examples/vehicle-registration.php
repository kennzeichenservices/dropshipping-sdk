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

// NOTE: Vehicle registration is a BETA feature of the dropshipping API (2.3.2).
// The request and response shapes may still change.

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
            deregistered: false,
            vehicleType: VehicleRegistrationVehicleType::Car,
            electronicInsuranceConfirmationNumber: 'ABC1234',        // eVB-Nummer, exactly 7 chars
            vehicleIdentificationNumber: 'WBA12345678901234',        // VIN / FIN
            vehicleTitleSecurityCode: 'ABCDEF123456',                // ZB II security code, exactly 12 chars
            iban: 'DE89370400440532013000',
            bic: 'COBADEFFXXX',
            vehicleRegistrationCertificateSecurityCode: 'SEC1234',   // optional, ZB I, exactly 7 chars
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
echo "Identity verification vendor: {$response->identityVerificationVendorId}\n";

// The customer MUST complete this form — it collects the identification data and
// signatures required for the registration. Nothing is processed until they do.
echo "Send the customer to: {$response->customerInputFormUrl}\n";

// Registration results arrive asynchronously via webhooks (spec 3.2.0), as typed events:
//
//   VEHICLE_REGISTRATION_IDENTITY_VERIFICATION_INITIALIZED / _SUCCEEDED / _FAILED
//   VEHICLE_REGISTRATION_DOCUMENT_SIGNATURE_INITIALIZED / _SUCCEEDED / _FAILED
//   VEHICLE_REGISTRATION_XKFZ_EVENT  — the registration office verdict and the assigned plate
//
// See webhooks.php for the full event handling setup.
