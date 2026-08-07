<?php

declare(strict_types=1);

namespace Dropshipping\DTO;

use Dropshipping\Enums\VehicleRegistrationLicensePlateNumberAssignmentStrategyType;

/**
 * Interface for the license plate number assignment strategies of a vehicle
 * registration.
 *
 * The API discriminates the concrete strategy by the `strategyType` property,
 * so every implementation serializes its own {@see $strategyType} value.
 *
 * @experimental Vehicle registration is a beta feature of the dropshipping API
 *               (2.4.0) and may change without a major version bump.
 */
interface VehicleRegistrationLicensePlateNumberAssignmentStrategyInterface
{
    /**
     * The discriminator value identifying this strategy.
     */
    public function strategyType(): VehicleRegistrationLicensePlateNumberAssignmentStrategyType;

    /**
     * Convert the strategy to an associative array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array;
}
