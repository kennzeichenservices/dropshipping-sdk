<?php

declare(strict_types=1);

namespace Dropshipping\DTO\Requests;

use Dropshipping\DTO\Address;
use Dropshipping\Enums\VehicleRegistrationVehicleHolderType;
use Dropshipping\Exceptions\DropshippingException;
use Dropshipping\Support\Validator;

/**
 * Request DTO representing a natural person as the vehicle holder in a vehicle
 * registration request, in the shape API versions up to 2.6.0 expect.
 *
 * Unlike {@see LicensePlateReservationVehicleHolder}, where the birth details are
 * optional, the registration API requires both the place of birth and the birth
 * date.
 *
 * ## Relation to the 2.7.0 holder variants
 *
 * 2.7.0 added the discriminator `vehicleHolder.type` and made it required. This
 * class predates it and deliberately keeps sending the payload without it, so
 * integrations on 2.4.0 to 2.6.0 are unaffected by the SDK update. For 2.7.0 use
 * {@see VehicleRegistrationNaturalPersonVehicleHolder}, which carries the same
 * fields plus `type: NATURAL_PERSON`, or
 * {@see VehicleRegistrationLegalPersonVehicleHolder} for a company.
 *
 * Sending this holder to 2.7.0 is rejected with 400 for the missing `type`.
 */
final readonly class VehicleRegistrationVehicleHolder implements VehicleRegistrationVehicleHolderInterface
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
     * Null, because this holder sends no discriminator. It is what tells
     * {@see VehicleRegistrationRequest} that the payload targets an API version
     * before 2.7.0.
     */
    public function holderType(): ?VehicleRegistrationVehicleHolderType
    {
        return null;
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
