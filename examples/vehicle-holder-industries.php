<?php

declare(strict_types=1);

require_once __DIR__ . '/_bootstrap.php';

// NOTE: The industry listing needs dropshipping API 2.7.0. Set DROPSHIPPING_API_VERSION=2.7.0 or
// pass apiVersion to DropshippingConfig, and make sure your client is enabled for that version.
// Below 2.7.0 the API answers 403 Forbidden, as it does for every endpoint on a version the client
// is not entitled to.

$industries = $client->vehicleHolderIndustries->getAll();

echo 'Vehicle holder industries: ' . count($industries->vehicleHolderIndustries) . "\n";
echo 'Distinct codes: ' . count($industries->codes()) . "\n\n";

// Printed with separators rather than padded columns on purpose: the names carry umlauts, and
// printf pads by bytes, so a %-40s column drifts by one position per multibyte character.
foreach ($industries->groupedBySection() as $sectionName => $entries) {
    echo $sectionName . "\n";

    foreach ($entries as $entry) {
        echo '  ' . implode(' | ', [$entry->code, $entry->name]) . "\n";
    }
}

echo "\n";

// A code is not unique: the spec says it may belong to several industry names, so the lookup
// returns every match rather than hiding the rest behind the first.
foreach ($industries->findByCode('G01') as $match) {
    echo "G01 covers: {$match->name}\n";
}

echo "\n";

// hasCode() is the check worth running before an order goes out: the API rejects an industry code
// it does not know, and it does so only after the customer has identified themselves and signed.
$industryCode = 'G01';

if ($industries->hasCode($industryCode)) {
    echo "{$industryCode} is accepted as vehicleHolder.industryCode\n";
} else {
    echo "{$industryCode} is not in the listing, pick another one\n";
}

// NOTE: the code goes into industryCode of DS::registrationLegalPersonVehicleHolder(), the vehicle
// holder of a registration ordered for a company. See vehicle-registration.php.
//
// The listing is not paginated and mirrors a KBA reference table (the Berufsschlüssel of the
// ErfassRL Kfz), so cache it on your side rather than fetching it per order.
