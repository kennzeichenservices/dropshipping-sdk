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
    'reservationApproval/with-duration' =>
        'Unreleased — webhooks 3.3.0 added reservationDurationDays to the reservation approval '
        . 'event, so the DTO gained the property. The baseline DTO drops the field on the floor, so '
        . 'a difference here is the point of the entry.',

    'reservationApproval/full' =>
        'Unreleased — same addition, seen from a payload that predates the field: the new property '
        . 'reads null instead of being absent. Every field the old DTO carried is unchanged.',

    'reservationApproval/no-pin' =>
        'Unreleased — same as above. All three entries can go once the compared-against tag '
        . 'carries the field.',
];
