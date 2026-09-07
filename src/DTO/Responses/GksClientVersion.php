<?php

declare(strict_types=1);

namespace Dropshipping\DTO\Responses;

use Dropshipping\Support\Hydrator;

/**
 * Response DTO representing a single GKS client version.
 *
 * A version is identified by its number alone; the API reports which
 * ones are currently enabled for the client.
 */
final readonly class GksClientVersion
{
    private const CONTEXT = 'GksClientVersion';

    /**
     * @param string $versionNumber Version number of the GKS client, e.g. "2.0" or "3.0".
     */
    public function __construct(
        public string $versionNumber,
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
            versionNumber: Hydrator::requireString($data, 'versionNumber', self::CONTEXT),
        );
    }
}
