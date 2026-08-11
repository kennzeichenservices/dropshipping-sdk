<?php

declare(strict_types=1);

namespace Dropshipping\DTO;

use Dropshipping\Enums\VehicleRegistrationLicensePlateNumberAssignmentStrategyType;

/**
 * Assignment strategy retaining the number of the previous license plate.
 *
 * Pair this with {@see VehicleRegistrationCustomization::$previousLicensePlate}
 * so the API knows which number to carry over. That rules the strategy out for
 * service type code NZ, which forbids the previous plate — see the "Rules the
 * API enforces but does not publish" section on {@see VehicleRegistrationCustomization}.
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
