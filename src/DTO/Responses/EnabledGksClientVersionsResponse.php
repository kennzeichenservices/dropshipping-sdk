<?php

declare(strict_types=1);

namespace Dropshipping\DTO\Responses;

use Dropshipping\Support\Hydrator;

/**
 * Response DTO for the enabled GKS client versions listing.
 *
 * Contains every {@see GksClientVersion} a GKS configuration may
 * currently reference.
 */
final readonly class EnabledGksClientVersionsResponse
{
    private const CONTEXT = 'EnabledGksClientVersionsResponse';

    /**
     * @param list<GksClientVersion> $gksClientVersions The list of enabled GKS client versions.
     */
    public function __construct(
        public array $gksClientVersions,
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
            gksClientVersions: array_map(
                static fn (array $item): GksClientVersion => GksClientVersion::fromArray($item),
                Hydrator::requireArrayList($data, 'gksClientVersions', self::CONTEXT),
            ),
        );
    }

    /**
     * List the enabled version numbers, for validating a value before sending it.
     *
     * @return list<string>
     */
    public function versionNumbers(): array
    {
        return array_map(
            static fn (GksClientVersion $version): string => $version->versionNumber,
            $this->gksClientVersions,
        );
    }
}
