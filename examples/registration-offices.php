<?php

declare(strict_types=1);

require_once __DIR__ . '/_bootstrap.php';

// NOTE: The registration office listing needs dropshipping API 2.6.0. Set
// DROPSHIPPING_API_VERSION=2.6.0 or pass apiVersion to DropshippingConfig, and make sure your
// client is enabled for that version. Below 2.6.0 the API answers 403 Forbidden, as it does for
// every endpoint on a version the client is not entitled to.

$offices = $client->registrationOffices->getAll();

echo 'Registration offices: ' . count($offices->registrationOffices) . "\n\n";

// Printed with separators rather than padded columns on purpose: office names and region codes
// carry umlauts, and printf pads by bytes, so a %-28s column drifts by one position per
// multibyte character.
foreach ($offices->registrationOffices as $office) {
    echo implode(' | ', [
        $office->id,
        $office->regionCode,
        $office->name,
        $office->district,
        $office->stateName,
        $office->reservationPrice . ' EUR',
    ]) . "\n";
}

echo "\n";

// A region code is not unique: several offices can issue the same one, so the lookup returns
// every match rather than hiding the rest behind the first. Codes carry umlauts (BÖ, LÖ, MÜ), so
// pass them exactly as the listing publishes them.
foreach ($offices->findByRegionCode('BO') as $match) {
    echo "BO is issued by {$match->id}: {$match->name}\n";
}

echo "\n";

// reservationPrice is the fee the office charges in advance for a license plate reservation, as a
// decimal string. chargesReservationFee() compares it numerically, so "0", "0.00" and "0.0000"
// all count as free; a value that cannot be read as a number counts as a fee, which is the safer
// of the two mistakes.
$office = $offices->findById(1);

if ($office === null) {
    echo "Office 1 is not in the listing.\n";
} elseif ($office->chargesReservationFee()) {
    echo "{$office->name} charges {$office->reservationPrice} EUR before reserving a plate\n";
} else {
    echo "{$office->name} reserves plates free of charge\n";
}

// NOTE: the spec does not say whether this id is the same identifier as the
// registrationOfficeServiceId that the availability check and the license plate reservation take.
// The names differ and so do the formats (int64 here against int32 there), which reads like an
// office and a service of that office are two things. Confirm with the API before passing one for
// the other. See check-license-plate-availability.php and reserve-license-plate.php.
//
// The listing is not paginated and changes rarely, so cache it on your side rather than fetching
// it per order. It is the same content as the registration office CSV export of the dropshipping
// self service.
