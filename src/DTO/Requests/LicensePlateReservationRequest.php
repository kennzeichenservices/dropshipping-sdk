<?php

declare(strict_types=1);

namespace Dropshipping\DTO\Requests;

final readonly class LicensePlateReservationRequest
{
    public function __construct(
        public string $email,
        public LicensePlateReservationCustomization $customization,
        public LicensePlateReservationVehicleHolder $vehicleHolder,
        public ?string $externalOrderId = null,
    ) {
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return array_filter([
            'externalOrderId' => $this->externalOrderId,
            'email' => $this->email,
            'customization' => $this->customization->toArray(),
            'vehicleHolder' => $this->vehicleHolder->toArray(),
        ], static fn (mixed $value): bool => $value !== null);
    }
}
