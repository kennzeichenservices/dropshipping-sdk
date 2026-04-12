<?php

declare(strict_types=1);

namespace Dropshipping\Enums;

/**
 * Represents the type of product in an order.
 */
enum ProductType: string
{
    case LicensePlate = 'LICENSE_PLATE';
    case VehicleDeregistration = 'VEHICLE_DEREGISTRATION';
    case Other = 'OTHER';
}
