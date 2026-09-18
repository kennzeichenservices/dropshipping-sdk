<?php

declare(strict_types=1);

namespace Dropshipping\DTO;

use Dropshipping\Enums\VehicleRegistrationDeliveryConfigurationDeliveryOption;

/**
 * Delivery configuration posting the document to a third party at an address of
 * its own — the deviating delivery address of a registration.
 */
final readonly class VehicleRegistrationDeliveryToThirdPartyDeliveryConfiguration implements VehicleRegistrationDeliveryConfigurationInterface
{
    /**
     * @param VehicleRegistrationDeliveryConfigurationRecipientInterface $recipient The third party the document is sent to, carrying the delivery address.
     */
    public function __construct(
        public VehicleRegistrationDeliveryConfigurationRecipientInterface $recipient,
    ) {
    }

    public function deliveryOption(): VehicleRegistrationDeliveryConfigurationDeliveryOption
    {
        return VehicleRegistrationDeliveryConfigurationDeliveryOption::DeliveryToThirdParty;
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
