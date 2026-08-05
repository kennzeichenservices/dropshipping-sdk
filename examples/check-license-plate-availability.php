<?php

declare(strict_types=1);

require_once __DIR__ . '/_bootstrap.php';

use Dropshipping\DS;
use Dropshipping\Enums\{LicensePlateType, VehicleType};

$response = $client->products->checkLicensePlateAvailability(
    DS::availabilityCheck(
        registrationOfficeServiceId: 1,
        city: 'B',
        middle: 'AB',
        end: '1234',
        licensePlateType: LicensePlateType::Regular,
        vehicleType: VehicleType::Car,
    ),
);

if (empty($response->availableLicensePlateNumbers)) {
    echo "No available license plates found.\n";
} else {
    echo "Available license plates:\n";
    foreach ($response->availableLicensePlateNumbers as $plate) {
        echo "  {$plate->city} {$plate->middle} {$plate->end}\n";
    }
}
