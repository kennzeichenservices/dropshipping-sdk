<?php

declare(strict_types=1);

namespace Dropshipping\DTO\Responses;

use Dropshipping\Support\Hydrator;

/**
 * Response DTO for a single industry (Wirtschaftszweig) a legal person can be
 * registered under.
 *
 * One entry of the listing published by API 2.7.0, taken from the Berufsschlüssel
 * of the ErfassRL Kfz, a KBA reference table. The {@see $code} goes into
 * `vehicleHolder.industryCode` of a vehicle registration ordered for a legal
 * person.
 *
 * Note that a code is not unique in the listing: the same code can appear under
 * several names, so matching on the code alone can return more than one entry.
 * See {@see VehicleHolderIndustriesResponse::findByCode()}.
 */
final readonly class VehicleHolderIndustry
{
    private const CONTEXT = 'VehicleHolderIndustry';

    /**
     * @param string $code        Code of the industry, three characters of A–Z and 0–9, e.g. "G01". Not unique, see the class docblock.
     * @param string $sectionName Section the industry belongs to, e.g. "G Handel".
     * @param string $name        Name of the industry (Dienstleistung/Tätigkeit), e.g. "Handel mit Kraftfahrzeugen, Kraftwagenteilen und -zubehör".
     */
    public function __construct(
        public string $code,
        public string $sectionName,
        public string $name,
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
            code: Hydrator::requireString($data, 'code', self::CONTEXT),
            sectionName: Hydrator::requireString($data, 'sectionName', self::CONTEXT),
            name: Hydrator::requireString($data, 'name', self::CONTEXT),
        );
    }

    /**
     * The section letter the industry is filed under, e.g. "G" for "G Handel".
     *
     * Read from the section name rather than the code, because the two are not
     * guaranteed to agree. An empty string is returned for a section name that
     * does not start with a letter, which the API should not send.
     */
    public function sectionLetter(): string
    {
        $first = mb_substr($this->sectionName, 0, 1);

        return preg_match('/^\p{L}$/u', $first) === 1 ? $first : '';
    }
}
