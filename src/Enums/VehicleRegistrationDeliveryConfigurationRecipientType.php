<?php

declare(strict_types=1);

namespace Dropshipping\Enums;

/**
 * Discriminator for the recipient of a vehicle registration document.
 * Serialized as the `type` property of the recipient object.
 */
enum VehicleRegistrationDeliveryConfigurationRecipientType: string
{
    /** A private individual, identified by name, gender and birth date. */
    case NaturalPerson = 'NATURAL_PERSON';

    /** A company or other legal entity, identified by its name. */
    case LegalPerson = 'LEGAL_PERSON';
}
