<?php

declare(strict_types=1);

require_once __DIR__ . '/_bootstrap.php';

use Dropshipping\DS;
use Dropshipping\Enums\Gender;

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

$response = $client->orders->createEmissionStickerOrder(
    DS::emissionStickerOrder(
        externalId: 'sticker-001',
        email: 'max@example.com',
        deliveryAddress: $address,
        invoiceAddress: $address,
        plate: DS::plate('B', 'AB', '1234'),
        electric: false,
        emissionKeyNumber: '0005',
        filePaths: ['/path/to/fahrzeugschein.pdf'],
    ),
);

echo "Emission sticker order created: {$response->id}\n";
