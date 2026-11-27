<?php

declare(strict_types=1);

namespace Dropshipping\DTO\Responses;

use Dropshipping\Enums\VehicleRegistrationDeliveryConfigurationDeliveryOption;
use Dropshipping\Support\Hydrator;

/**
 * How a single registration document reaches its addressee, as the API returns
 * it.
 *
 * One class for all three delivery options rather than the request classes: a
 * read should not fail because the API returns an option this SDK version does
 * not model. Such an option comes back as null.
 */
final readonly class VehicleRegistrationDeliveryConfigurationDetails
{
    private const CONTEXT = 'VehicleRegistrationDeliveryConfigurationDetails';

    /**
     * @param VehicleRegistrationDeliveryConfigurationDeliveryOption|null   $deliveryOption The delivery option, null where this SDK version does not know it.
     * @param VehicleRegistrationDeliveryConfigurationRecipientDetails|null $recipient      The third party, null where the document goes to the vehicle holder.
     */
    public function __construct(
        public ?VehicleRegistrationDeliveryConfigurationDeliveryOption $deliveryOption = null,
        public ?VehicleRegistrationDeliveryConfigurationRecipientDetails $recipient = null,
    ) {
    }

    /**
     * Create an instance from a raw API response array.
     *
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $recipient = Hydrator::optionalArray($data, 'recipient', self::CONTEXT);

        return new self(
            deliveryOption: Hydrator::enumOrNull(VehicleRegistrationDeliveryConfigurationDeliveryOption::class, $data, 'deliveryOption'),
            recipient: $recipient === null ? null : VehicleRegistrationDeliveryConfigurationRecipientDetails::fromArray($recipient),
        );
    }
}
