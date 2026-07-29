<?php

declare(strict_types=1);

require_once __DIR__ . '/_bootstrap.php';

use Dropshipping\DS;
use Dropshipping\Enums\{
    Gender,
    VehicleRegistrationLicensePlateNumberAssignmentStrategy,
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
            // RANDOM: the office picks a number. RESERVATION: requires reservationPin.
            // RETAINMENT: keeps the number of previousLicensePlate.
            licensePlateNumberAssignmentStrategy: VehicleRegistrationLicensePlateNumberAssignmentStrategy::Random,
            vehicleRegistrationServiceTypeCode: VehicleRegistrationServiceTypeCode::NZ, // Neuzulassung
            plate: DS::plate('B', 'AB', '1234'),
            deregistered: false,
            vehicleType: VehicleRegistrationVehicleType::Car,
            licensePlateType: VehicleRegistrationLicensePlateType::Regular,
            electronicInsuranceConfirmationNumber: 'ABC1234',        // eVB-Nummer, exactly 7 chars
            vehicleIdentificationNumber: 'WBA12345678901234',        // VIN / FIN
            vehicleTitleSecurityCode: 'ABCDEF123456',                // ZB II security code, exactly 12 chars
            iban: 'DE89370400440532013000',
            bic: 'COBADEFFXXX',
            vehicleRegistrationCertificateSecurityCode: 'SEC1234',   // optional, ZB I, exactly 7 chars
            vehicleTitleNumber: 'AB123456',                          // optional, exactly 8 chars
        ),
        vehicleHolderAddress: $address,
        externalOrderId: 'registration-001',  // optional
        gksConfigurationId: 'your-gks-uuid',  // optional
        contractPartnerKopaKey: 'K123X',      // optional
    )
);

echo "Registration order created: {$response->orderId}\n";
echo "Identity verification vendor: {$response->identityVerificationVendorId}\n";

// The customer MUST complete this form — it collects the identification data and
// signatures required for the registration. Nothing is processed until they do.
echo "Send the customer to: {$response->customerInputFormUrl}\n";

// Registration results arrive asynchronously via webhooks. Those event types are not
// described in any published webhooks spec yet, so enable unknown-event tolerance to
// receive them as UnknownWebhookEvent instead of an exception:
//
//   $pipeline = DS::webhookPipeline($secret, tolerateUnknownEvents: true);
//
// See webhooks.php for the full event handling setup.
