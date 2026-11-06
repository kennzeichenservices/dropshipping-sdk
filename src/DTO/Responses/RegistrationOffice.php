<?php

declare(strict_types=1);

namespace Dropshipping\DTO\Responses;

use Dropshipping\Support\Hydrator;

/**
 * Response DTO for a single registration office (Zulassungsstelle).
 *
 * One entry of the listing published by API 2.6.0, the same content as the
 * registration office CSV export of the dropshipping self service.
 *
 * Note the spec does not say whether `id` is the same identifier as the
 * `registrationOfficeServiceId` an availability check or a license plate
 * reservation takes. The names differ and so do the formats, int64 here against
 * int32 there, which reads like an office and a service of that office are two
 * things. Confirm with the API before passing one for the other.
 */
final readonly class RegistrationOffice
{
    private const CONTEXT = 'RegistrationOffice';

    /**
     * @param int    $id               ID of the office. See the class docblock on how this relates to `registrationOfficeServiceId`.
     * @param string $regionCode       Region code shown on the plate, e.g. "BO". May carry umlauts, e.g. "BÖ".
     * @param string $name             Name of the office, e.g. "Bochum (Stadt)".
     * @param string $district         Registration district (Zulassungsbezirk), e.g. "Stadt Bochum".
     * @param string $stateName        Federal state (Bundesland), e.g. "Nordrhein-Westfalen".
     * @param string $reservationPrice License plate reservation fee the office charges in advance, as a decimal string with a period as the separator. Kept as a string because it is a billed cent amount.
     */
    public function __construct(
        public int $id,
        public string $regionCode,
        public string $name,
        public string $district,
        public string $stateName,
        public string $reservationPrice,
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
            id: Hydrator::requireInt($data, 'id', self::CONTEXT),
            regionCode: Hydrator::requireString($data, 'regionCode', self::CONTEXT),
            name: Hydrator::requireString($data, 'name', self::CONTEXT),
            district: Hydrator::requireString($data, 'district', self::CONTEXT),
            stateName: Hydrator::requireString($data, 'stateName', self::CONTEXT),
            reservationPrice: Hydrator::requireString($data, 'reservationPrice', self::CONTEXT),
        );
    }

    /**
     * Whether the office charges a reservation fee in advance.
     *
     * Compares the decimal string numerically, so "0", "0.00" and "0.0000" all
     * count as free. A value the API sends in some other shape cannot be read as
     * a number and is treated as a fee, which is the safer of the two mistakes.
     */
    public function chargesReservationFee(): bool
    {
        if (!is_numeric($this->reservationPrice)) {
            return true;
        }

        return (float) $this->reservationPrice > 0.0;
    }
}
