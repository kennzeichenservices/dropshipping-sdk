<?php

declare(strict_types=1);

namespace Dropshipping\DTO;

use Dropshipping\Enums\VehicleRegistrationLicensePlateType;
use Dropshipping\Support\Validator;

/**
 * Data transfer object describing the license plate a vehicle registration
 * should use, e.g. the plate behind a previously reserved number.
 *
 * @experimental Vehicle registration is a beta feature of the dropshipping API
 *               (2.3.2) and may change without a major version bump.
 */
final readonly class VehicleRegistrationLicensePlate
{
    /**
     * @param EuroLicensePlateNumberComponents     $licensePlateNumberComponents Components of the license plate number.
     * @param VehicleRegistrationLicensePlateType  $licensePlateType             Type of license plate to issue.
     * @param int|null                             $seasonStartMonth             Start month for seasonal plates (1–12).
     * @param int|null                             $seasonEndMonth               End month for seasonal plates (1–12).
     */
    public function __construct(
        public EuroLicensePlateNumberComponents $licensePlateNumberComponents,
        public VehicleRegistrationLicensePlateType $licensePlateType,
        public ?int $seasonStartMonth = null,
        public ?int $seasonEndMonth = null,
    ) {
        Validator::requireNullableIntRange($seasonStartMonth, 'seasonStartMonth', 1, 12);
        Validator::requireNullableIntRange($seasonEndMonth, 'seasonEndMonth', 1, 12);
    }

    /**
     * Convert the license plate to an associative array, excluding null values.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'licensePlateNumberComponents' => $this->licensePlateNumberComponents->toArray(),
            'licensePlateType' => $this->licensePlateType->value,
            'seasonStartMonth' => $this->seasonStartMonth,
            'seasonEndMonth' => $this->seasonEndMonth,
        ], static fn (mixed $value): bool => $value !== null);
    }
}
