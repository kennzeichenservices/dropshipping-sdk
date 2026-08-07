<?php

declare(strict_types=1);

/**
 * Behaviour changes that have been reviewed and deliberately accepted.
 *
 * bc-check reports any hydration difference as a regression, because it cannot tell
 * an intended change from an accident. Record accepted ones here with the reason and
 * the release they shipped in, so the check goes green again and the next unexpected
 * difference still stands out.
 *
 * Remove an entry once the compared-against tag is newer than the change — at that
 * point both sides agree again and the entry only hides future differences.
 *
 * Keys are corpus labels (see corpus.php).
 *
 * @return array<string, string>
 */

return [
    'orderCreation/non-sequential-deliveries' =>
        '2.3.24 — array_values() reindexes deliveries. The property is declared list<Delivery>, '
        . 'so the previous keyed array violated its own contract and made $deliveries[0] fail. '
        . 'Only reachable if the API sends a JSON object instead of an array.',

    'availability/non-sequential' =>
        '2.3.24 — same reindexing as above for availableLicensePlateNumbers.',

    'vehicleRegistrationResponse' =>
        'Unreleased — dropshipping API 2.4.0 dropped identityVerificationVendorId and '
        . 'customerInputFormUrl from the vehicle registration response, so the DTO dropped them '
        . 'too. Made while the feature was still @experimental, in the same release that takes '
        . 'that marker off; the customer-facing URLs now arrive as VEHICLE_REGISTRATION_*_INITIALIZED '
        . 'webhook events.',
];
