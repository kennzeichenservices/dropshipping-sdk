<?php

declare(strict_types=1);

namespace Dropshipping\DTO\Requests;

use Dropshipping\Support\Validator;

/**
 * Request DTO representing the company details of a GKS configuration.
 *
 * Contains the company name and address fields required when creating
 * or updating a GKS (Großkundenschnittstelle) configuration.
 */
final readonly class GksConfigurationCompany
{
    /**
     * @param string $name        Company name.
     * @param string $streetName  Street name of the company address.
     * @param string $houseNumber House number of the company address.
     * @param string $zipCode     Postal code of the company address.
     * @param string $cityName    City name of the company address.
     * @param string $countryCode ISO country code of the company address.
     */
    public function __construct(
        public string $name,
        public string $streetName,
        public string $houseNumber,
        public string $zipCode,
        public string $cityName,
        public string $countryCode,
    ) {
        Validator::requireNonEmpty($name, 'name');
        Validator::requireNonEmpty($streetName, 'streetName');
        Validator::requireNonEmpty($houseNumber, 'houseNumber');
        Validator::requireNonEmpty($zipCode, 'zipCode');
        Validator::requireNonEmpty($cityName, 'cityName');
        Validator::requireNonEmpty($countryCode, 'countryCode');
    }

    /**
     * Convert the company data to an associative array.
     *
     * @return array<string, string>
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'streetName' => $this->streetName,
            'houseNumber' => $this->houseNumber,
            'zipCode' => $this->zipCode,
            'cityName' => $this->cityName,
            'countryCode' => $this->countryCode,
        ];
    }
}
