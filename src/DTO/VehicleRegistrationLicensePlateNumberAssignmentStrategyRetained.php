<?php

declare(strict_types=1);

namespace Dropshipping\DTO;

use Dropshipping\Enums\VehicleRegistrationLicensePlateNumberAssignmentStrategyType;

/**
 * Assignment strategy retaining the number of the previous license plate.
 *
 * The strategy carries no data of its own; the number to carry over comes from
 * {@see VehicleRegistrationCustomization::$previousLicensePlate}, which the
 * customization therefore insists on — without its seals, which stay unread on
 * a plate that is not handed in. Both rules, and why NZ cannot use this
 * strategy at all, are in the "Rules the API enforces but does not publish"
 * section on {@see VehicleRegistrationCustomization}.
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
