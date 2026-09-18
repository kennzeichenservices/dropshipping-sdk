<?php

declare(strict_types=1);

namespace Dropshipping\DTO;

use Dropshipping\Enums\Gender;
use Dropshipping\Enums\VehicleRegistrationDeliveryConfigurationRecipientType;
use Dropshipping\Exceptions\DropshippingException;
use Dropshipping\Support\Validator;

/**
 * Recipient of a vehicle registration document who is a private individual.
 */
final readonly class VehicleRegistrationDeliveryConfigurationNaturalPersonRecipient implements VehicleRegistrationDeliveryConfigurationRecipientInterface
{
    /**
     * @param VehicleRegistrationDeliveryConfigurationAddress $address   Address the document is delivered to.
     * @param string                                          $firstName First name of the recipient, 1–100 characters.
     * @param string                                          $lastName  Last name of the recipient, 1–100 characters.
     * @param Gender                                          $gender    Gender of the recipient.
     * @param string                                          $birthDate Birth date of the recipient in ISO 8601 format (e.g. 1990-01-31).
     *
     * @throws DropshippingException When a value violates the API constraints.
     */
    public function __construct(
        public VehicleRegistrationDeliveryConfigurationAddress $address,
        public string $firstName,
        public string $lastName,
        public Gender $gender,
        public string $birthDate,
    ) {
        Validator::requireStringLength($firstName, 'firstName', 1, 100);
        Validator::requireStringLength($lastName, 'lastName', 1, 100);
        Validator::requireNonEmpty($birthDate, 'birthDate');
    }

    public function type(): VehicleRegistrationDeliveryConfigurationRecipientType
    {
        return VehicleRegistrationDeliveryConfigurationRecipientType::NaturalPerson;
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
            'firstName' => $this->firstName,
            'lastName' => $this->lastName,
            'gender' => $this->gender->value,
            'birthDate' => $this->birthDate,
        ];
    }
}
