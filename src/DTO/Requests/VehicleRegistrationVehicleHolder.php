<?php

declare(strict_types=1);

namespace Dropshipping\DTO\Requests;

use Dropshipping\DTO\Address;
use Dropshipping\Exceptions\DropshippingException;
use Dropshipping\Support\Validator;

/**
 * Request DTO representing the vehicle holder in a vehicle registration request.
 *
 * Unlike {@see LicensePlateReservationVehicleHolder}, where the birth details are
 * optional, the registration API requires both the place of birth and the birth
 * date.
 *
 * @experimental Vehicle registration is a beta feature of the dropshipping API
 *               (2.3.2) and may change without a major version bump.
 */
final readonly class VehicleRegistrationVehicleHolder
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
