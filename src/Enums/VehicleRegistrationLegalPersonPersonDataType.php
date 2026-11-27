<?php

declare(strict_types=1);

namespace Dropshipping\Enums;

/**
 * Where the data of a legal person vehicle holder comes from: sent with the
 * registration, or taken from a durable power of attorney.
 *
 * Discriminator of `vehicleHolder.personData` for a holder of type
 * `LEGAL_PERSON`, published by API 2.7.0. The spec names the schema
 * `VehicleRegistrationRequestLegalPersonVehicleHolderPersonDataType`, shortened
 * here. Only ever sent, so there is no placeholder case for unknown values.
 */
enum VehicleRegistrationLegalPersonPersonDataType: string
{
    /**
     * The data is taken from a durable power of attorney named by its ID. It
     * has to be COMPLETE, active and not expired.
     */
    case DurablePowerOfAttorneyReference = 'DURABLE_POWER_OF_ATTORNEY_REFERENCE';

    /**
     * The data is sent with the registration.
     */
    case Provided = 'PROVIDED';
}
