<?php

declare(strict_types=1);

namespace Dropshipping\DTO\Requests;

use Dropshipping\DTO\Address;

final readonly class LicensePlateReservationVehicleHolder
{
    public function __construct(
        public Address $address,
        public ?string $birthDate = null,
        public ?string $placeOfBirth = null,
    ) {
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return array_filter([
            'address' => $this->address->toArray(),
            'birthDate' => $this->birthDate,
            'placeOfBirth' => $this->placeOfBirth,
        ], static fn (mixed $value): bool => $value !== null);
    }
}
