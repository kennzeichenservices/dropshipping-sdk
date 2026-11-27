<?php

declare(strict_types=1);

namespace Dropshipping\DTO\Responses;

use Dropshipping\Enums\Gender;
use Dropshipping\Enums\VehicleRegistrationDeliveryConfigurationRecipientType;
use Dropshipping\Support\Hydrator;

/**
 * The third party a registration document is handed to, as the API returns it.
 *
 * One class for both recipient types. Which fields are set follows from
 * {@see $type}: a legal person has a {@see $name}, a natural person the
 * remaining ones.
 */
final readonly class VehicleRegistrationDeliveryConfigurationRecipientDetails
{
    private const CONTEXT = 'VehicleRegistrationDeliveryConfigurationRecipientDetails';

    /**
     * @param VehicleRegistrationDeliveryConfigurationRecipientType|null  $type      The recipient type, null where this SDK version does not know it.
     * @param VehicleRegistrationDeliveryConfigurationAddressDetails|null $address   Address of the recipient.
     * @param string|null                                                 $name      Name of a legal person.
     * @param string|null                                                 $firstName First name of a natural person.
     * @param string|null                                                 $lastName  Last name of a natural person.
     * @param Gender|null                                                 $gender    Gender of a natural person, null where this SDK version does not know the value.
     * @param string|null                                                 $birthDate Birth date of a natural person, YYYY-MM-DD.
     */
    public function __construct(
        public ?VehicleRegistrationDeliveryConfigurationRecipientType $type = null,
        public ?VehicleRegistrationDeliveryConfigurationAddressDetails $address = null,
        public ?string $name = null,
        public ?string $firstName = null,
        public ?string $lastName = null,
        public ?Gender $gender = null,
        public ?string $birthDate = null,
    ) {
    }

    /**
     * Create an instance from a raw API response array.
     *
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $address = Hydrator::optionalArray($data, 'address', self::CONTEXT);

        return new self(
            type: Hydrator::enumOrNull(VehicleRegistrationDeliveryConfigurationRecipientType::class, $data, 'type'),
            address: $address === null ? null : VehicleRegistrationDeliveryConfigurationAddressDetails::fromArray($address),
            name: Hydrator::optionalString($data, 'name', self::CONTEXT),
            firstName: Hydrator::optionalString($data, 'firstName', self::CONTEXT),
            lastName: Hydrator::optionalString($data, 'lastName', self::CONTEXT),
            gender: Hydrator::enumOrNull(Gender::class, $data, 'gender'),
            birthDate: Hydrator::optionalString($data, 'birthDate', self::CONTEXT),
        );
    }
}
