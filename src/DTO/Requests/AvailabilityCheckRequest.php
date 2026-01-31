<?php

declare(strict_types=1);

namespace Dropshipping\DTO\Requests;

use Dropshipping\Enums\LicensePlateType;
use Dropshipping\Enums\VehicleType;

final readonly class AvailabilityCheckRequest
{
    public function __construct(
        public int $registrationOfficeServiceId,
        public string $city,
        public string $middle,
        public string $end,
        public LicensePlateType $licensePlateType,
        public VehicleType $vehicleType,
        public ?int $seasonStartMonth = null,
        public ?int $seasonEndMonth = null,
    ) {
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return array_filter([
            'registrationOfficeServiceId' => $this->registrationOfficeServiceId,
            'licensePlateNumberPatternComponents' => [
                'city' => $this->city,
                'middle' => $this->middle,
                'end' => $this->end,
            ],
            'licensePlateType' => $this->licensePlateType->value,
            'vehicleType' => $this->vehicleType->value,
            'seasonStartMonth' => $this->seasonStartMonth,
            'seasonEndMonth' => $this->seasonEndMonth,
        ], static fn (mixed $value): bool => $value !== null);
    }
}
