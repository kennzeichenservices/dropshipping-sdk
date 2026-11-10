<?php

declare(strict_types=1);

namespace Dropshipping\DTO\Responses;

use Dropshipping\Support\Hydrator;

/**
 * Response DTO for the vehicle holder industry listing.
 *
 * Carries every {@see VehicleHolderIndustry} a legal person can be registered
 * under. Published by API 2.7.0 alongside the legal person vehicle holder, whose
 * `industryCode` has to be one of these codes.
 *
 * The listing is not paginated and changes rarely (it mirrors a KBA reference
 * table), so it is worth caching on the caller's side rather than fetching it per
 * order.
 */
final readonly class VehicleHolderIndustriesResponse
{
    private const CONTEXT = 'VehicleHolderIndustriesResponse';

    /**
     * @param list<VehicleHolderIndustry> $vehicleHolderIndustries The industries.
     */
    public function __construct(
        public array $vehicleHolderIndustries,
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
            vehicleHolderIndustries: array_map(
                static fn (array $item): VehicleHolderIndustry => VehicleHolderIndustry::fromArray($item),
                Hydrator::requireArrayList($data, 'vehicleHolderIndustries', self::CONTEXT),
            ),
        );
    }

    /**
     * All industries published under one code.
     *
     * A code is not unique: the spec says it may belong to several industry
     * names, so this returns a list rather than a single entry. Any of them
     * confirms the code is accepted as `vehicleHolder.industryCode`; an empty
     * list means it is not.
     *
     * The comparison is exact, so pass the code as the API publishes it, in
     * upper case.
     *
     * @return list<VehicleHolderIndustry>
     */
    public function findByCode(string $code): array
    {
        return array_values(array_filter(
            $this->vehicleHolderIndustries,
            static fn (VehicleHolderIndustry $industry): bool => $industry->code === $code,
        ));
    }

    /**
     * Whether the listing knows a code, for validating an industry code before
     * an order is placed.
     */
    public function hasCode(string $code): bool
    {
        return $this->findByCode($code) !== [];
    }

    /**
     * Every code the listing publishes, deduplicated and in the order the API
     * sent them.
     *
     * @return list<string>
     */
    public function codes(): array
    {
        return array_values(array_unique(array_map(
            static fn (VehicleHolderIndustry $industry): string => $industry->code,
            $this->vehicleHolderIndustries,
        )));
    }

    /**
     * The industries grouped by their section name, for building a two-level
     * picker. Sections keep the order the API sent them in.
     *
     * The key type is `array-key`, not `string`: PHP casts a numeric array key
     * to an int, so a section name the API sends as digits only would come back
     * as an int. The spec constrains the name to 1–255 characters and nothing
     * else, so that cannot be ruled out here.
     *
     * @return array<array-key, list<VehicleHolderIndustry>>
     */
    public function groupedBySection(): array
    {
        $grouped = [];

        foreach ($this->vehicleHolderIndustries as $industry) {
            $grouped[$industry->sectionName][] = $industry;
        }

        return $grouped;
    }
}
