<?php

declare(strict_types=1);

namespace Dropshipping\DTO;

use Dropshipping\Enums\VehicleRegistrationLicensePlateNumberAssignmentStrategyType;

/**
 * Assignment strategy letting the registration office pick an arbitrary
 * available license plate number.
 *
 * @experimental Vehicle registration is a beta feature of the dropshipping API
 *               (2.3.2) and may change without a major version bump.
 */
final readonly class VehicleRegistrationLicensePlateNumberAssignmentStrategyRandom implements VehicleRegistrationLicensePlateNumberAssignmentStrategyInterface
{
    public function strategyType(): VehicleRegistrationLicensePlateNumberAssignmentStrategyType
    {
        return VehicleRegistrationLicensePlateNumberAssignmentStrategyType::Random;
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
