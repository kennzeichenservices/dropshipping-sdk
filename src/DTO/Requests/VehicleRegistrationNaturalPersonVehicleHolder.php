<?php

declare(strict_types=1);

namespace Dropshipping\DTO\Requests;

use Dropshipping\DTO\Address;
use Dropshipping\Enums\VehicleRegistrationVehicleHolderType;
use Dropshipping\Exceptions\DropshippingException;
use Dropshipping\Support\Validator;

/**
 * Request DTO representing a natural person as the vehicle holder, with the
 * `type` discriminator API 2.7.0 requires.
 *
 * Carries the same fields as {@see VehicleRegistrationVehicleHolder} and adds
 * `type: NATURAL_PERSON` to the payload. Which of the two to use follows from
 * the API version the integration runs against, nothing else:
 *
 * - 2.7.0 and up: this class. The discriminator is required there.
 * - 2.4.0 to 2.6.0: {@see VehicleRegistrationVehicleHolder}, which omits it.
 *
 * @see VehicleRegistrationLegalPersonVehicleHolder for a company as the holder.
 */
final readonly class VehicleRegistrationNaturalPersonVehicleHolder implements VehicleRegistrationVehicleHolderInterface
{
    /**
     * @param Address     $address      Address of the vehicle holder.
     * @param string      $placeOfBirth Place of birth of the vehicle holder, 1–150 characters.
     * @param string      $birthDate    Birth date of the vehicle holder in ISO 8601 format (e.g. 1990-01-31).
     * @param string|null $birthName    Optional birth name of the vehicle holder, 1–100 characters.
     *
     * @throws DropshippingException When a value violates the API constraints.
     */
    public function __construct(
        public Address $address,
        public string $placeOfBirth,
        public string $birthDate,
        public ?string $birthName = null,
    ) {
        Validator::requireStringLength($placeOfBirth, 'placeOfBirth', 1, 150);
        Validator::requireNonEmpty($birthDate, 'birthDate');
        Validator::requireNullableStringLength($birthName, 'birthName', 1, 100);
    }

    /**
     * Narrowed to a non-nullable return: only the pre-2.7.0 holder sends no
     * discriminator.
     */
    public function holderType(): VehicleRegistrationVehicleHolderType
    {
        return VehicleRegistrationVehicleHolderType::NaturalPerson;
    }

    public function address(): Address
    {
        return $this->address;
    }

    /**
     * Always null: only a legal person can have a customs major customer ID.
     */
    public function customsMajorCustomerId(): ?string
    {
        return null;
    }

    /**
     * Convert the vehicle holder data to an associative array, omitting the
     * optional birth name when null.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $data = [
            'type' => VehicleRegistrationVehicleHolderType::NaturalPerson->value,
            'address' => $this->address->toArray(),
            'placeOfBirth' => $this->placeOfBirth,
            'birthDate' => $this->birthDate,
        ];

        if ($this->birthName !== null) {
            $data['birthName'] = $this->birthName;
        }

        return $data;
    }
}
