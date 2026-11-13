<?php

declare(strict_types=1);

namespace Dropshipping\Enums;

/**
 * The known reasons a VEHICLE_REGISTRATION_IDENTITY_VERIFICATION_FAILED webhook
 * event names in its `message` when the holder is a legal person.
 *
 * Ahead of the identification the provider checks the company against the
 * register and the named person against the company's representatives. Where
 * that check refuses the order, the event carries one of these keys and no
 * identification link was ever handed out.
 *
 * The webhooks spec declares `message` as a plain string and lists no values,
 * so this enum is not part of hydration: the event keeps the raw string, and
 * {@see self::fromMessage()} maps it where it is one of the keys below. Anything
 * else, a free text of the identification vendor for example, or a key added
 * later, yields null rather than an exception.
 */
enum VehicleRegistrationIdentityVerificationFailureReason: string
{
    /** The company was not found in the register. */
    case CompanyNotFound = 'company_not_found';

    /**
     * The company check did not come to a positive result. The provider also
     * sends this as `company_verification_not_verified`, see {@see self::fromMessage()}.
     */
    case CompanyNotVerified = 'company_not_verified';

    /** The company is not confirmed as active. */
    case CompanyNotActive = 'company_not_active';

    /** The company name does not match the one the order was placed with. */
    case CompanyNameMismatch = 'company_name_mismatch';

    /** The company register number is missing or does not match the order. */
    case CompanyNumberMismatch = 'company_number_mismatch';

    /** First or last name of the person to be identified is missing. */
    case MissingHolderName = 'missing_holder_name';

    /** The named person is not among the representatives of the company. */
    case NoMatchingRepresentative = 'no_matching_representative';

    /** The named person matches more than one representative of the company. */
    case AmbiguousRepresentative = 'ambiguous_representative';

    /** The representative found is not entitled to sign for the company alone. */
    case MissingSoleSigningAuthority = 'missing_sole_signing_authority';

    /** The company or representative check failed without a specific reason. */
    case CompanyRepresentativeVerificationFailed = 'company_representative_verification_failed';

    /**
     * Map the `message` of a failed identity verification to its reason.
     *
     * Tolerant on purpose, because the field is free text: surrounding whitespace
     * and letter case are ignored, and `company_verification_not_verified`, which
     * the provider sends for the same outcome, resolves to
     * {@see self::CompanyNotVerified}.
     *
     * @param string|null $message The raw `message` of the event.
     *
     * @return self|null The reason, or null for a missing message and for any
     *                   text that is not one of the known keys.
     */
    public static function fromMessage(?string $message): ?self
    {
        if ($message === null) {
            return null;
        }

        $key = strtolower(trim($message));

        return match ($key) {
            'company_verification_not_verified' => self::CompanyNotVerified,
            default => self::tryFrom($key),
        };
    }
}
