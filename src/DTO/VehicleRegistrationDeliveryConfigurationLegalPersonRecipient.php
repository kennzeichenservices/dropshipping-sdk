<?php

declare(strict_types=1);

namespace Dropshipping\DTO;

use Dropshipping\Enums\VehicleRegistrationDeliveryConfigurationRecipientType;
use Dropshipping\Exceptions\DropshippingException;
use Dropshipping\Support\Validator;

/**
 * Recipient of a vehicle registration document who is a company or other legal
 * entity — a dealership or leasing company taking the Zulassungsbescheinigung
 * Teil II, for instance.
 */
final readonly class VehicleRegistrationDeliveryConfigurationLegalPersonRecipient implements VehicleRegistrationDeliveryConfigurationRecipientInterface
{
    /**
     * @param VehicleRegistrationDeliveryConfigurationAddress $address Address the document is delivered to.
     * @param string                                          $name    Name of the legal entity, 1–100 characters.
     *
     * @throws DropshippingException When a value violates the API constraints.
     */
    public function __construct(
        public VehicleRegistrationDeliveryConfigurationAddress $address,
        public string $name,
    ) {
        Validator::requireStringLength($name, 'name', 1, 100);
    }

    public function type(): VehicleRegistrationDeliveryConfigurationRecipientType
    {
        return VehicleRegistrationDeliveryConfigurationRecipientType::LegalPerson;
    }

    public function address(): VehicleRegistrationDeliveryConfigurationAddress
    {
        return $this->address;
    }

    /**
     * Convert the recipient to an associative array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'type' => $this->type()->value,
            'address' => $this->address->toArray(),
            'name' => $this->name,
        ];
    }
}
