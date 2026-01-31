<?php

declare(strict_types=1);

namespace Dropshipping\DTO\Responses;

use Dropshipping\DTO\EuroLicensePlateNumberComponents;

final readonly class AvailabilityCheckResponse
{
    /**
     * @param list<EuroLicensePlateNumberComponents> $availableLicensePlateNumbers
     */
    public function __construct(
        public array $availableLicensePlateNumbers,
    ) {
    }

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            availableLicensePlateNumbers: array_map(
                static fn (array $item): EuroLicensePlateNumberComponents => EuroLicensePlateNumberComponents::fromArray($item),
                $data['availableLicensePlateNumbers'] ?? [],
            ),
        );
    }
}
