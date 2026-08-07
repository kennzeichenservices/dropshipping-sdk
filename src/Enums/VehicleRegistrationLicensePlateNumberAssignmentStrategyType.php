<?php

declare(strict_types=1);

namespace Dropshipping\Enums;

/**
 * Discriminator for the license plate number assignment strategy of a vehicle
 * registration. Serialized as the `strategyType` property of the strategy object.
 */
enum VehicleRegistrationLicensePlateNumberAssignmentStrategyType: string
{
    /** The registration office assigns an arbitrary available number. */
    case Random = 'RANDOM';

    /** A previously reserved number is used — requires the plate and its reservation PIN. */
    case Reservation = 'RESERVATION';

    /** The number of the previous license plate is retained. */
    case Retainment = 'RETAINMENT';
}
