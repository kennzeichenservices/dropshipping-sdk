<?php

declare(strict_types=1);

namespace Dropshipping\DTO;

use Dropshipping\Enums\VehicleRegistrationDeliveryConfigurationRecipientType;

/**
 * Interface for the recipients a vehicle registration document can be handed to.
 *
 * The API discriminates the concrete recipient by the `type` property, so every
 * implementation serializes its own {@see $type} value alongside the address the
 * document goes to.
 */
interface VehicleRegistrationDeliveryConfigurationRecipientInterface
{
    /**
     * The discriminator value identifying this kind of recipient.
     */
    public function type(): VehicleRegistrationDeliveryConfigurationRecipientType;

    /**
     * The address the document is delivered to.
     */
    public function address(): VehicleRegistrationDeliveryConfigurationAddress;

    /**
     * Convert the recipient to an associative array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array;
}
