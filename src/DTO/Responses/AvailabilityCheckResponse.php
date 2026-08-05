<?php

declare(strict_types=1);

namespace Dropshipping\DTO\Responses;

use Dropshipping\DTO\EuroLicensePlateNumberComponents;
use Dropshipping\Support\Hydrator;

/**
 * Response DTO for a license plate availability check.
 *
 * Contains a list of available license plate number combinations
 * returned by the availability check endpoint.
 */
final readonly class AvailabilityCheckResponse
{
    private const CONTEXT = 'AvailabilityCheckResponse';

    /**
     * Create a new availability check response instance.
     *
     * @param list<EuroLicensePlateNumberComponents> $availableLicensePlateNumbers
     */
    public function __construct(
        public array $availableLicensePlateNumbers,
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
            availableLicensePlateNumbers: array_map(
                static fn (array $item): EuroLicensePlateNumberComponents => EuroLicensePlateNumberComponents::fromArray($item),
                Hydrator::optionalArrayList($data, 'availableLicensePlateNumbers', self::CONTEXT),
            ),
        );
    }
}
