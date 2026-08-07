<?php

declare(strict_types=1);

namespace Dropshipping\DTO;

use Dropshipping\Enums\VehicleRegistrationLicensePlateNumberAssignmentStrategyType;

/**
 * Assignment strategy retaining the number of the previous license plate.
 *
 * Pair this with {@see VehicleRegistrationCustomization::$previousLicensePlate}
 * so the API knows which number to carry over.
 *
 * @experimental Vehicle registration is a beta feature of the dropshipping API
 *               (2.4.0) and may change without a major version bump.
 */
final readonly class VehicleRegistrationLicensePlateNumberAssignmentStrategyRetained implements VehicleRegistrationLicensePlateNumberAssignmentStrategyInterface
{
    public function strategyType(): VehicleRegistrationLicensePlateNumberAssignmentStrategyType
    {
        return VehicleRegistrationLicensePlateNumberAssignmentStrategyType::Retainment;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'strategyType' => $this->strategyType()->value,
        ];
    }
}
