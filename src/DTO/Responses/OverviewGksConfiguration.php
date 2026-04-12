<?php

declare(strict_types=1);

namespace Dropshipping\DTO\Responses;

/**
 * Response DTO representing a GKS configuration overview.
 *
 * Contains the UUID identifier and display name of a
 * GKS (Großkundenschnittstelle) configuration.
 */
final readonly class OverviewGksConfiguration
{
    /**
     * @param string $id   UUID of the GKS configuration.
     * @param string $name Display name of the GKS configuration.
     */
    public function __construct(
        public string $id,
        public string $name,
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
            id: $data['id'],
            name: $data['name'],
        );
    }
}
