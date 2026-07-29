<?php

declare(strict_types=1);

namespace Dropshipping\Enums;

/**
 * Determines how the license plate number for a vehicle registration is assigned.
 *
 * @experimental Vehicle registration is a beta feature of the dropshipping API
 *               (2.3.2) and may change without a major version bump.
 */
enum VehicleRegistrationLicensePlateNumberAssignmentStrategy: string
{
    /** The registration office assigns an arbitrary available number. */
    case Random = 'RANDOM';

    /** A previously reserved number is used — requires a reservation PIN. */
    case Reservation = 'RESERVATION';

    /** The number of the previous license plate is retained. */
    case Retainment = 'RETAINMENT';
}
