<?php

declare(strict_types=1);

namespace Dropshipping\Enums;

/**
 * Discriminates the kind of vehicle holder a vehicle registration is ordered for.
 *
 * Published by API 2.7.0, which splits `VehicleRegistrationRequestVehicleHolder`
 * into a natural and a legal person variant and makes `type` the discriminator
 * between them. Up to 2.6.0 the holder was always a natural person and the field
 * did not exist, which is why the SDK keeps a holder DTO that leaves it out, see
 * {@see \Dropshipping\DTO\Requests\VehicleRegistrationVehicleHolderInterface}.
 */
enum VehicleRegistrationVehicleHolderType: string
{
    /** A private individual, identified by place and date of birth. */
    case NaturalPerson = 'NATURAL_PERSON';

    /**
     * A company or other legal entity (juristische Person), identified by its
     * name, company register number and industry. Not supported for the service
     * type code `HA`.
     */
    case LegalPerson = 'LEGAL_PERSON';
}
