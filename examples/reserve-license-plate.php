<?php

declare(strict_types=1);

require_once __DIR__ . '/_bootstrap.php';

use Dropshipping\DS;
use Dropshipping\Enums\{Gender, LicensePlateType, VehicleType};

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

$response = $client->shipments->createLicensePlateReservation(
    DS::licensePlateReservation(
        email: 'max@example.com',
        customization: DS::reservationCustomization(
            registrationOfficeServiceId: 1,
            licensePlateType: LicensePlateType::Regular,
            vehicleType: VehicleType::Car,
            plate: DS::plate('B', 'AB', '1234'),
        ),
        vehicleHolder: DS::reservationVehicleHolder(
            address: $address,
            birthDate: '1985-06-15',  // optional
            placeOfBirth: 'Berlin',   // optional
        ),
    )
);

echo "Reservation created: {$response->id}\n";
