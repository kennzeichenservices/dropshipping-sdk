<?php

declare(strict_types=1);

namespace Dropshipping\DTO\Webhooks;

use Dropshipping\Support\Hydrator;

/**
 * Represents a single cost breakdown line item in a vehicle registration webhook event.
 *
 * Contains the item number, optional code and name, the amount in cents,
 * and an optional note.
 *
 * @experimental Vehicle registration webhook events are new in the dropshipping
 *               webhooks API (3.2.0) and may change without a major version bump.
 */
final readonly class VehicleRegistrationCostBreakdownItem
{
    private const CONTEXT = 'VehicleRegistrationCostBreakdownItem';

    /**
     * @param int         $number Item position number.
     * @param string|null $code   Optional cost item code.
     * @param string|null $name   Optional cost item name.
     * @param int         $amount Amount in cents.
     * @param string|null $note   Optional note for this cost item.
     */
    public function __construct(
        public int $number,
        public ?string $code,
        public ?string $name,
        public int $amount,
        public ?string $note,
    ) {
    }

    /**
     * Create an instance from a raw data array.
     *
     * @param array<string, mixed> $data
     *
     * @return self
     */
    public static function fromArray(array $data): self
    {
        return new self(
            number: Hydrator::requireInt($data, 'number', self::CONTEXT),
            code: Hydrator::optionalString($data, 'code', self::CONTEXT),
            name: Hydrator::optionalString($data, 'name', self::CONTEXT),
            amount: Hydrator::requireInt($data, 'amount', self::CONTEXT),
            note: Hydrator::optionalString($data, 'note', self::CONTEXT),
        );
    }
}
