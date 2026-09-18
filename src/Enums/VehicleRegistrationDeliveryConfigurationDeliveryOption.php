<?php

declare(strict_types=1);

namespace Dropshipping\Enums;

/**
 * Discriminator for how a vehicle registration document is handed over.
 * Serialized as the `deliveryOption` property of the delivery configuration object.
 */
enum VehicleRegistrationDeliveryConfigurationDeliveryOption: string
{
    /** The document is sent to the vehicle holder — the default, carrying no recipient of its own. */
    case DeliveryToOwner = 'DELIVERY_TO_OWNER';

    /** The document is sent to a third party at its own address. */
    case DeliveryToThirdParty = 'DELIVERY_TO_THIRD_PARTY';

    /** The document is collected from the registration office by a third party. */
    case PickupByThirdParty = 'PICKUP_BY_THIRD_PARTY';
}
