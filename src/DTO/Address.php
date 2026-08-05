<?php

declare(strict_types=1);

namespace Dropshipping\DTO;

use Dropshipping\Enums\Gender;
use Dropshipping\Support\Hydrator;
use Dropshipping\Support\Validator;

/**
 * Data transfer object representing a postal address.
 */
final readonly class Address
{
    private const CONTEXT = 'Address';

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
            firstName: Hydrator::requireString($data, 'firstName', self::CONTEXT),
            lastName: Hydrator::requireString($data, 'lastName', self::CONTEXT),
            gender: Hydrator::requireEnum(Gender::class, $data, 'gender', self::CONTEXT),
            streetName: Hydrator::requireString($data, 'streetName', self::CONTEXT),
            houseNumber: Hydrator::requireString($data, 'houseNumber', self::CONTEXT),
            zipCode: Hydrator::requireString($data, 'zipCode', self::CONTEXT),
            cityName: Hydrator::requireString($data, 'cityName', self::CONTEXT),
            countryCode: Hydrator::requireString($data, 'countryCode', self::CONTEXT),
            taxNumber: Hydrator::optionalString($data, 'taxNumber', self::CONTEXT),
            companyName: Hydrator::optionalString($data, 'companyName', self::CONTEXT),
            additionalField: Hydrator::optionalString($data, 'additionalField', self::CONTEXT),
            phoneNumber: Hydrator::optionalString($data, 'phoneNumber', self::CONTEXT),
        );
    }
}
