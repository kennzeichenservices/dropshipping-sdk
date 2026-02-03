<?php

declare(strict_types=1);

namespace Dropshipping\DTO\Requests;

use Dropshipping\DTO\Address;

/**
 * DTO representing the vehicle holder in a license plate reservation request.
 *
 * Contains the holder's address and optional birth details.
 */
final readonly class LicensePlateReservationVehicleHolder
{
    /**
     * @param Address     $address      Address of the vehicle holder.
     * @param string|null $birthDate    Optional birth date of the vehicle holder.
     * @param string|null $placeOfBirth Optional place of birth of the vehicle holder.
     */
    public function __construct(
        public Address $address,
        public ?string $birthDate = null,
        public ?string $placeOfBirth = null,
    ) {
    }

    /**
     * Convert the vehicle holder data to an associative array, excluding null values.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'address' => $this->address->toArray(),
            'birthDate' => $this->birthDate,
            'placeOfBirth' => $this->placeOfBirth,
        ], static fn (mixed $value): bool => $value !== null);
    }
}
