<?php

declare(strict_types=1);

namespace Dropshipping\Support;

/**
 * Rules the API applies to an IBAN that the SDK checks ahead of it.
 *
 * @internal
 */
final class Iban
{
    /**
     * Country prefixes whose IBAN needs a BIC alongside it (API 2.7.0): Monaco,
     * San Marino and Switzerland are in SEPA but outside the EU, so the debit is
     * not routed on the IBAN alone.
     */
    private const BIC_REQUIRING_PREFIXES = ['MC', 'SM', 'CH'];

    private function __construct()
    {
    }

    /**
     * Whether an IBAN has to be accompanied by a BIC.
     *
     * The country is read from the first two characters, upper cased first so a
     * lower case IBAN is judged the same way; the API itself rejects a malformed
     * IBAN.
     */
    public static function requiresBic(string $iban): bool
    {
        return in_array(mb_strtoupper(mb_substr($iban, 0, 2)), self::BIC_REQUIRING_PREFIXES, true);
    }
}
