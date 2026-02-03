<?php

declare(strict_types=1);

namespace Dropshipping\Enums;

/**
 * Represents how a license plate is used, such as standard Euro plates or parking plates.
 */
enum LicensePlateUsageType: string
{
    case Euro = 'EURO';
    case Parking = 'PARKING';
}
