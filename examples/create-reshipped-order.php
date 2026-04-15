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

// returnedDeliveryId comes from a DELIVERY_RETURN webhook event ($event->delivery->id)
$response = $client->orders->createReshippedOrder(
    DS::reshippedOrder(
        externalId: 'reship-001',
        returnedDeliveryId: 456,
        deliveryAddress: $address,
        invoiceAddress: $address,
    )
);

echo "Reshipped order created: {$response->id}\n";
