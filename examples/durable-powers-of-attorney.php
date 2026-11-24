<?php

declare(strict_types=1);

require_once __DIR__ . '/_bootstrap.php';

use Dropshipping\DS;
use Dropshipping\Exceptions\ApiException;

// NOTE: Durable powers of attorney need dropshipping API 2.7.0. Set DROPSHIPPING_API_VERSION=2.7.0 or
// pass apiVersion to DropshippingConfig. Below 2.7.0 the API answers 403 Forbidden.

// --- Create ----------------------------------------------------------------------------------------
//
// Creating one starts a real signing process: the signer gets an invitation by email. Run this part
// only with an address you control.

$request = DS::durablePowerOfAttorney(
    company: DS::durablePowerOfAttorneyCompany(
        name: 'Muster Autohaus GmbH',
        // From $client->vehicleHolderIndustries->getAll(), see vehicle-holder-industries.php.
        industryCode: 'G01',
        streetName: 'Hauptstraße',
        houseNumber: '12A',
        zipCode: '10115',
        cityName: 'Berlin',
        // Either a SEPA direct debit mandate (BIC only needed for MC, SM and CH) ...
        taxData: DS::durablePowerOfAttorneySepaTaxData(iban: 'DE89370400440532013000'),
        // ... or the major customer ID of the customs administration instead:
        // taxData: DS::durablePowerOfAttorneyCustomsTaxData('123A4567'),
        number: 'HRB 123456',
    ),
    signerFirstName: 'Erika',
    signerLastName: 'Musterfrau',
    signerEmail: 'erika.musterfrau@example.com',
    // Omitted, the API sets the maximum: one year from today.
    expirationDate: null,
);

$created = $client->vehicleRegistrations->createDurablePowerOfAttorney($request);

echo "Created {$created->id}, status {$created->status->value}, expires {$created->expirationDate}\n";

// The status is INCOMPLETE now. COMPLETE or ERROR arrives as a webhook, see webhooks.php:
// VEHICLE_REGISTRATION_DURABLE_POWER_OF_ATTORNEY_COMPLETION / _ERROR, both carrying the ID.

// --- List and look up --------------------------------------------------------------------------------

$all = $client->vehicleRegistrations->getDurablePowersOfAttorney();

foreach ($all->durablePowersOfAttorney as $powerOfAttorney) {
    echo implode(' | ', [
        $powerOfAttorney->id,
        $powerOfAttorney->company->name,
        $powerOfAttorney->status->value,
        $powerOfAttorney->active ? 'active' : 'revoked',
        'expires ' . $powerOfAttorney->expirationDate,
    ]) . "\n";
}

// Signed, not revoked, not expired.
echo count($all->usable()) . " usable\n";

try {
    $single = $client->vehicleRegistrations->getDurablePowerOfAttorney($created->id);
    echo "{$single->id} is " . ($single->isUsable() ? 'usable' : 'not usable yet') . "\n";
} catch (ApiException $e) {
    echo "Lookup failed with {$e->getStatusCode()}: {$e->getMessage()}\n";
}
