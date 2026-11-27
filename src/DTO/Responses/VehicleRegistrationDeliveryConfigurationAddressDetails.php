<?php

declare(strict_types=1);

namespace Dropshipping\DTO\Responses;

use Dropshipping\Support\Hydrator;

/**
 * Address a registration document is delivered to, as the API returns it.
 *
 * The read-side counterpart of
 * {@see \Dropshipping\DTO\VehicleRegistrationDeliveryConfigurationAddress},
 * without its length checks.
 */
final readonly class VehicleRegistrationDeliveryConfigurationAddressDetails
{
    private const CONTEXT = 'VehicleRegistrationDeliveryConfigurationAddressDetails';

    public function __construct(
        public ?string $streetName = null,
        public ?string $houseNumber = null,
        public ?string $zipCode = null,
        public ?string $cityName = null,
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
            streetName: Hydrator::optionalString($data, 'streetName', self::CONTEXT),
            houseNumber: Hydrator::optionalString($data, 'houseNumber', self::CONTEXT),
            zipCode: Hydrator::optionalString($data, 'zipCode', self::CONTEXT),
            cityName: Hydrator::optionalString($data, 'cityName', self::CONTEXT),
        );
    }
}
