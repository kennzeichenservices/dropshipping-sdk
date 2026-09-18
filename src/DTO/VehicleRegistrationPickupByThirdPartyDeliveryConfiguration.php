<?php

declare(strict_types=1);

namespace Dropshipping\DTO;

use Dropshipping\Enums\VehicleRegistrationDeliveryConfigurationDeliveryOption;

/**
 * Delivery configuration leaving the document at the registration office for a
 * third party to collect.
 *
 * Nothing is posted, but the recipient still carries an address: the office
 * records who is entitled to pick the document up, and identifies them by it.
 */
final readonly class VehicleRegistrationPickupByThirdPartyDeliveryConfiguration implements VehicleRegistrationDeliveryConfigurationInterface
{
    /**
     * @param VehicleRegistrationDeliveryConfigurationRecipientInterface $recipient The third party entitled to collect the document.
     */
    public function __construct(
        public VehicleRegistrationDeliveryConfigurationRecipientInterface $recipient,
    ) {
    }

    public function deliveryOption(): VehicleRegistrationDeliveryConfigurationDeliveryOption
    {
        return VehicleRegistrationDeliveryConfigurationDeliveryOption::PickupByThirdParty;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'deliveryOption' => $this->deliveryOption()->value,
            'recipient' => $this->recipient->toArray(),
        ];
    }
}
