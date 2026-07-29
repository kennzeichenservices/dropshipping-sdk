<?php

declare(strict_types=1);

namespace Dropshipping\Enums;

/**
 * Represents the type of vehicle in a vehicle registration request.
 *
 * Narrower than {@see VehicleDeregistrationVehicleType}: the registration
 * API accepts only cars, motorcycles and trailers.
 *
 * @experimental Vehicle registration is a beta feature of the dropshipping API
 *               (2.3.2) and may change without a major version bump.
 */
enum VehicleRegistrationVehicleType: string
{
    case Car = 'CAR';
    case Motorcycle = 'MOTORCYCLE';
    case Trailer = 'TRAILER';
}
