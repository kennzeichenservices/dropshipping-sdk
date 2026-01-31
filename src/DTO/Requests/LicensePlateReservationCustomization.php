<?php

declare(strict_types=1);

namespace Dropshipping\DTO\Requests;

use Dropshipping\DTO\EuroLicensePlateNumberComponents;
use Dropshipping\Enums\LicensePlateType;
use Dropshipping\Enums\VehicleType;

final readonly class LicensePlateReservationCustomization
{
    public function __construct(
        public int $registrationOfficeServiceId,
        public LicensePlateType $licensePlateType,
        public VehicleType $vehicleType,
        public EuroLicensePlateNumberComponents $licensePlateNumberComponents,
        public ?int $seasonStartMonth = null,
        public ?int $seasonEndMonth = null,
    ) {
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return array_filter([
            'registrationOfficeServiceId' => $this->registrationOfficeServiceId,
            'licensePlateType' => $this->licensePlateType->value,
            'vehicleType' => $this->vehicleType->value,
            'licensePlateNumberComponents' => $this->licensePlateNumberComponents->toArray(),
            'seasonStartMonth' => $this->seasonStartMonth,
            'seasonEndMonth' => $this->seasonEndMonth,
        ], static fn (mixed $value): bool => $value !== null);
    }

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            registrationOfficeServiceId: $data['registrationOfficeServiceId'],
            licensePlateType: LicensePlateType::from($data['licensePlateType']),
            vehicleType: VehicleType::from($data['vehicleType']),
            licensePlateNumberComponents: EuroLicensePlateNumberComponents::fromArray($data['licensePlateNumberComponents']),
            seasonStartMonth: $data['seasonStartMonth'] ?? null,
            seasonEndMonth: $data['seasonEndMonth'] ?? null,
        );
    }
}
