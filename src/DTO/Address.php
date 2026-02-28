<?php

declare(strict_types=1);

namespace Dropshipping\DTO;

use Dropshipping\Enums\Gender;
use Dropshipping\Support\Validator;

/**
 * Data transfer object representing a postal address.
 */
final readonly class Address
{
    /**
     * Create a new Address instance.
     */
    public function __construct(
        public string $firstName,
        public string $lastName,
        public Gender $gender,
        public string $streetName,
        public string $houseNumber,
        public string $zipCode,
        public string $cityName,
        public string $countryCode,
        public ?string $taxNumber = null,
        public ?string $companyName = null,
        public ?string $additionalField = null,
        public ?string $phoneNumber = null,
    ) {
        Validator::requireStringLength($firstName, 'firstName', 1, 100);
        Validator::requireStringLength($lastName, 'lastName', 1, 100);
        Validator::requireStringLength($streetName, 'streetName', 1, 100);
        Validator::requireStringLength($houseNumber, 'houseNumber', 1, 10);
        Validator::requireStringLength($zipCode, 'zipCode', 1, 12);
        Validator::requireStringLength($cityName, 'cityName', 1, 100);
        Validator::requireStringLength($countryCode, 'countryCode', 1, 2);
        Validator::requireNullableStringLength($taxNumber, 'taxNumber', 1, 20);
        Validator::requireNullableStringLength($companyName, 'companyName', 1, 100);
        Validator::requireNullableStringLength($additionalField, 'additionalField', 1, 100);
        Validator::requireNullableStringLength($phoneNumber, 'phoneNumber', 1, 20);
    }

    /**
     * Convert the address to an associative array, excluding null values.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'firstName' => $this->firstName,
            'lastName' => $this->lastName,
            'gender' => $this->gender->value,
            'streetName' => $this->streetName,
            'houseNumber' => $this->houseNumber,
            'zipCode' => $this->zipCode,
            'cityName' => $this->cityName,
            'countryCode' => $this->countryCode,
            'taxNumber' => $this->taxNumber,
            'companyName' => $this->companyName,
            'additionalField' => $this->additionalField,
            'phoneNumber' => $this->phoneNumber,
        ], static fn (mixed $value): bool => $value !== null);
    }

    /**
     * Create an Address instance from an associative array.
     *
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            firstName: $data['firstName'],
            lastName: $data['lastName'],
            gender: Gender::from($data['gender']),
            streetName: $data['streetName'],
            houseNumber: $data['houseNumber'],
            zipCode: $data['zipCode'],
            cityName: $data['cityName'],
            countryCode: $data['countryCode'],
            taxNumber: $data['taxNumber'] ?? null,
            companyName: $data['companyName'] ?? null,
            additionalField: $data['additionalField'] ?? null,
            phoneNumber: $data['phoneNumber'] ?? null,
        );
    }
}
