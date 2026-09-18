<?php

declare(strict_types=1);

namespace Dropshipping\DTO;

use Dropshipping\Enums\VehicleRegistrationDeliveryConfigurationDeliveryOption;

/**
 * Interface for the ways a single vehicle registration document can reach its
 * addressee.
 *
 * The API discriminates the concrete configuration by the `deliveryOption`
 * property, so every implementation serializes its own {@see $deliveryOption}
 * value.
 */
interface VehicleRegistrationDeliveryConfigurationInterface
{
    /**
     * The discriminator value identifying this delivery option.
     */
    public function deliveryOption(): VehicleRegistrationDeliveryConfigurationDeliveryOption;

    /**
     * Convert the delivery configuration to an associative array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array;
}
