<?php

declare(strict_types=1);

namespace Dropshipping\Enums;

/**
 * The document a customer identifies themselves with during a vehicle registration.
 *
 * Published in API 2.6.0, where `identityVerificationDocumentType` is a required
 * field of the registration request. The choice is the customer's and is
 * independent of the verification vendor the API picks for it, so it does not
 * overlap with {@see \Dropshipping\DTO\Webhooks\VehicleRegistrationIdentityVerificationVendor}.
 *
 * The two `*_WITH_PROOF_OF_ADDRESS` members need a separate address document on
 * top of the ID itself, because neither a passport nor foreign papers carry a
 * German address.
 *
 * There is no `UNKNOWN` member: the field is only ever sent, never hydrated from
 * a response, so an unmodelled value cannot reach it.
 */
enum IdentityVerificationDocumentType: string
{
    /** German identity card (Personalausweis). */
    case GermanId = 'GERMAN_ID';

    /** Residence permit (Aufenthaltstitel). */
    case ResidencePermit = 'RESIDENCE_PERMIT';

    /** German passport (Reisepass), together with a proof of address. */
    case GermanPassportWithProofOfAddress = 'GERMAN_PASSPORT_WITH_PROOF_OF_ADDRESS';

    /** Foreign identity papers, together with a proof of address. */
    case ForeignPapersWithProofOfAddress = 'FOREIGN_PAPERS_WITH_PROOF_OF_ADDRESS';
}
