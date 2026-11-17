<?php

declare(strict_types=1);

namespace Dropshipping\DTO\Responses;

use Dropshipping\Support\Hydrator;

/**
 * The company of a durable power of attorney as the API returns it.
 *
 * The read-side counterpart of
 * {@see \Dropshipping\DTO\Requests\VehicleRegistrationDurablePowerOfAttorneyCompany}.
 * It is a separate class so that reading does not run the request validation:
 * a stored record that no longer meets a request constraint still loads.
 */
final readonly class VehicleRegistrationDurablePowerOfAttorneyCompanyDetails
{
    private const CONTEXT = 'VehicleRegistrationDurablePowerOfAttorneyCompanyDetails';

    public function __construct(
        public string $name,
        public string $streetName,
        public string $houseNumber,
        public string $zipCode,
        public string $cityName,
        public VehicleRegistrationDurablePowerOfAttorneyTaxDataDetails $taxData,
        public ?string $number = null,
    ) {
    }

    /**
     * Create an instance from a raw API response array.
     *
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            name: Hydrator::requireString($data, 'name', self::CONTEXT),
            streetName: Hydrator::requireString($data, 'streetName', self::CONTEXT),
            houseNumber: Hydrator::requireString($data, 'houseNumber', self::CONTEXT),
            zipCode: Hydrator::requireString($data, 'zipCode', self::CONTEXT),
            cityName: Hydrator::requireString($data, 'cityName', self::CONTEXT),
            taxData: VehicleRegistrationDurablePowerOfAttorneyTaxDataDetails::fromArray(
                Hydrator::requireArray($data, 'taxData', self::CONTEXT),
            ),
            number: Hydrator::optionalString($data, 'number', self::CONTEXT),
        );
    }
}
