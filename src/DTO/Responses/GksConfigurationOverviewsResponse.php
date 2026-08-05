<?php

declare(strict_types=1);

namespace Dropshipping\DTO\Responses;

/**
 * Response DTO for the GKS configuration overviews listing.
 *
 * Contains an array of {@see OverviewGksConfiguration} objects
 * representing all available GKS configurations.
 */
final readonly class GksConfigurationOverviewsResponse
{
    /**
     * @param list<OverviewGksConfiguration> $overviewGksConfigurations The list of GKS configuration overviews.
     */
    public function __construct(
        public array $overviewGksConfigurations,
    ) {
    }

    /**
     * Create an instance from a raw API response array.
     *
     * @param array<string, mixed> $data
     *
     * @return self
     */
    public static function fromArray(array $data): self
    {
        return new self(
            overviewGksConfigurations: array_values(array_map(
                static fn (array $item): OverviewGksConfiguration => OverviewGksConfiguration::fromArray($item),
                $data['overviewGksConfigurations'],
            )),
        );
    }
}
