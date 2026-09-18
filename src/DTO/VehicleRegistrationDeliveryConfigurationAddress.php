<?php

declare(strict_types=1);

namespace Dropshipping\DTO;

use Dropshipping\Exceptions\DropshippingException;
use Dropshipping\Support\Validator;

/**
 * Data transfer object representing the address a vehicle registration document
 * is delivered to.
 *
 * Deliberately not {@see Address}: the registration office addresses the
 * document to the recipient carrying it, so this address holds no name, gender
 * or country — the name comes from the recipient, and the office only delivers
 * within Germany.
 */
final readonly class VehicleRegistrationDeliveryConfigurationAddress
{
    /**
     * @param string $streetName  Street name, 1–100 characters.
     * @param string $houseNumber House number, 1–10 characters.
     * @param string $zipCode     Postal code, 1–12 characters.
     * @param string $cityName    City name, 1–100 characters.
     *
     * @throws DropshippingException When a value violates the API constraints.
     */
    public function __construct(
        public string $streetName,
        public string $houseNumber,
        public string $zipCode,
        public string $cityName,
    ) {
        Validator::requireStringLength($streetName, 'streetName', 1, 100);
        Validator::requireStringLength($houseNumber, 'houseNumber', 1, 10);
        Validator::requireStringLength($zipCode, 'zipCode', 1, 12);
        Validator::requireStringLength($cityName, 'cityName', 1, 100);
    }

    /**
     * Convert the address to an associative array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'streetName' => $this->streetName,
            'houseNumber' => $this->houseNumber,
            'zipCode' => $this->zipCode,
            'cityName' => $this->cityName,
        ];
    }
}
