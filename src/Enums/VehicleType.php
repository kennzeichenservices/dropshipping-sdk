<?php

declare(strict_types=1);

namespace Dropshipping\Enums;

/**
 * Represents the type of vehicle associated with a license plate.
 */
enum VehicleType: string
{
    case Car = 'CAR';
    case Motorcycle = 'MOTORCYCLE';
}
