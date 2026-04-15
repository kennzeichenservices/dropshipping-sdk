<?php

declare(strict_types=1);

require_once __DIR__ . '/_bootstrap.php';

use Dropshipping\DS;

$request = DS::gksConfiguration(
    name: 'My KBA Config',
    kopaKey: 'kopa-key-value',
    username: 'kba-username',
    password: 'kba-password',
    publicKeyCertificate: (string) file_get_contents('/path/to/cert.pem'),
    privateKey: (string) file_get_contents('/path/to/private.key'),
    company: DS::gksCompany(
        name: 'Musterfirma GmbH',
        streetName: 'Musterstraße',
        houseNumber: '1',
        zipCode: '12345',
        cityName: 'Berlin',
        countryCode: 'DE',
    ),
);

// Create
$created = $client->gksConfigurations->create($request);
echo "Created: {$created->id} ({$created->name})\n";

// Update
$client->gksConfigurations->update($created->id, $request);
echo "Updated: {$created->id}\n";

// List all
$overviews = $client->gksConfigurations->getOverviews();
foreach ($overviews->overviewGksConfigurations as $cfg) {
    echo "  {$cfg->id}: {$cfg->name}\n";
}

// Get single
$single = $client->gksConfigurations->getOverview($created->id);
echo "Fetched: {$single->id} ({$single->name})\n";
