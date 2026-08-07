<?php

declare(strict_types=1);

namespace Dropshipping\DTO;

use Dropshipping\Enums\VehicleRegistrationLicensePlateNumberAssignmentStrategyType;
use Dropshipping\Enums\VehicleRegistrationLicensePlateType;
use Dropshipping\Exceptions\DropshippingException;
use Dropshipping\Support\Validator;

/**
 * Assignment strategy letting the registration office pick an arbitrary
 * available license plate number.
 *
 * The number is the office's choice, but the kind of plate is not — pass the
 * desired {@see VehicleRegistrationLicensePlateType} and, for seasonal plates,
 * the season range.
 *
 * @experimental Vehicle registration is a beta feature of the dropshipping API
 *               (2.4.0) and may change without a major version bump.
 */
final readonly class VehicleRegistrationLicensePlateNumberAssignmentStrategyRandom implements VehicleRegistrationLicensePlateNumberAssignmentStrategyInterface
{
    /**
     * @param VehicleRegistrationLicensePlateType $licensePlateType Type of license plate to issue.
     * @param int|null                            $seasonStartMonth Start month for seasonal plates (1–12).
     * @param int|null                            $seasonEndMonth   End month for seasonal plates (1–12).
     *
     * @throws DropshippingException When a value violates the API constraints.
     */
    public function __construct(
        public VehicleRegistrationLicensePlateType $licensePlateType,
        public ?int $seasonStartMonth = null,
        public ?int $seasonEndMonth = null,
    ) {
        Validator::requireNullableIntRange($seasonStartMonth, 'seasonStartMonth', 1, 12);
        Validator::requireNullableIntRange($seasonEndMonth, 'seasonEndMonth', 1, 12);
    }

    public function strategyType(): VehicleRegistrationLicensePlateNumberAssignmentStrategyType
    {
        return VehicleRegistrationLicensePlateNumberAssignmentStrategyType::Random;
    }

    /**
     * Convert the strategy to an associative array, excluding null values.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'strategyType' => $this->strategyType()->value,
            'licensePlateType' => $this->licensePlateType->value,
            'seasonStartMonth' => $this->seasonStartMonth,
            'seasonEndMonth' => $this->seasonEndMonth,
        ], static fn (mixed $value): bool => $value !== null);
    }
}
