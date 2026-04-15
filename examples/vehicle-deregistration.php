<?php

declare(strict_types=1);

require_once __DIR__ . '/_bootstrap.php';

use Dropshipping\DS;
use Dropshipping\Enums\{Gender, VehicleDeregistrationLicensePlateType, VehicleDeregistrationVehicleType};

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

$response = $client->vehicleDeregistrations->createDeregistration(
    DS::vehicleDeregistration(
        email: 'max@example.com',
        customization: DS::deregistrationCustomization(
            vehicleType: VehicleDeregistrationVehicleType::Car,
            licensePlateType: VehicleDeregistrationLicensePlateType::Regular,
            plate: DS::plate('B', 'AB', '1234'),
            licensePlateReservationIncluded: false,
            vehicleIdentificationNumber: 'WBA12345678901234',
            vehicleRegistrationCertificateSecurityCode: 'ABC123',
            vehicleRegistrationDate: '2020-01-15',
            rearLicensePlateSecurityCode: 'XY9876',
        ),
        vehicleHolderAddress: $address,
        externalOrderId: 'deregistration-001', // optional
        gksConfigurationId: 'your-gks-uuid',   // optional
    )
);

echo "Deregistration order created: {$response->orderId}\n";

// Files are delivered via VEHICLE_DEREGISTRATION_XKFZ_EVENT webhooks.
// Use $client->vehicleDeregistrations->downloadFileContent($file->fileAccessKey)
// to download them. See webhooks.php for the event handling setup.
