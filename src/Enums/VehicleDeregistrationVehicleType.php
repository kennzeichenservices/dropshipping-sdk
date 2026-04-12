<?php

declare(strict_types=1);

namespace Dropshipping\Enums;

/**
 * Represents the type of vehicle for a vehicle deregistration request.
 *
 * Separate enum from {@see VehicleType} because the API spec defines
 * distinct vehicle type schemas for deregistration and license plate contexts.
 */
enum VehicleDeregistrationVehicleType: string
{
    case Car = 'CAR';
    case LightMotorcycle = 'LIGHT_MOTORCYCLE';
    case Motorcycle = 'MOTORCYCLE';
    case Other = 'OTHER';
    case Tractor = 'TRACTOR';
    case Trailer = 'TRAILER';
    case Truck = 'TRUCK';
}
