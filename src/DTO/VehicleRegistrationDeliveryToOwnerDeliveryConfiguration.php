<?php

declare(strict_types=1);

namespace Dropshipping\DTO;

use Dropshipping\Enums\VehicleRegistrationDeliveryConfigurationDeliveryOption;

/**
 * Delivery configuration sending the document to the vehicle holder.
 *
 * The configuration carries no data of its own: the address is the one on
 * {@see \Dropshipping\DTO\Requests\VehicleRegistrationVehicleHolder::$address}.
 * It is also what the registration office does when no
 * {@see VehicleRegistrationDeliveryConfigurations} is passed at all, so it only
 * needs spelling out for the document that keeps the default while the other
 * one deviates.
 */
final readonly class VehicleRegistrationDeliveryToOwnerDeliveryConfiguration implements VehicleRegistrationDeliveryConfigurationInterface
{
    public function deliveryOption(): VehicleRegistrationDeliveryConfigurationDeliveryOption
    {
        return VehicleRegistrationDeliveryConfigurationDeliveryOption::DeliveryToOwner;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'deliveryOption' => $this->deliveryOption()->value,
        ];
    }
}
