<?php

declare(strict_types=1);

namespace Dropshipping\DTO\Requests;

use Dropshipping\DTO\Address;

/**
 * Request DTO representing the vehicle holder in a vehicle registration request.
 *
 * Contains only the holder's address, unlike
 * {@see LicensePlateReservationVehicleHolder} which also includes birth details.
 *
 * @experimental Vehicle registration is a beta feature of the dropshipping API
 *               (2.3.2) and may change without a major version bump.
 */
final readonly class VehicleRegistrationVehicleHolder
{
    /**
     * @param Address $address Address of the vehicle holder.
     */
    public function __construct(
        public Address $address,
    ) {
    }

    /**
     * Convert the vehicle holder data to an associative array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'address' => $this->address->toArray(),
        ];
    }
}
