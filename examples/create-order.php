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

$item = DS::orderItem(
    productVariantId: 42,
    name: 'Zulassung',
    sku: 'ZL-001',
    quantity: 1,
    plate: DS::plate('B', 'AB', '1234'),
);

$response = $client->orders->create(
    DS::order(
        externalId: 'order-001',
        email: 'max@example.com',
        deliveryAddress: $address,
        invoiceAddress: $address,
        items: [$item],
    ),
);

echo "Order created: {$response->id}\n";
