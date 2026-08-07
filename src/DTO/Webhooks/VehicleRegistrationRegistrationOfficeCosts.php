<?php

declare(strict_types=1);

namespace Dropshipping\DTO\Webhooks;

use Dropshipping\Support\Hydrator;

/**
 * Represents the registration office costs in a vehicle registration webhook event.
 *
 * Contains a list of individual cost breakdown items, an optional total summary item,
 * and an optional note.
 *
 * The webhooks spec marks `total` as required. It is modelled as nullable here on
 * purpose, matching {@see VehicleDeregistrationRegistrationOfficeCosts}: an otherwise
 * usable cost breakdown should still reach the consumer if the field is absent.
 */
final readonly class VehicleRegistrationRegistrationOfficeCosts
{
    private const CONTEXT = 'VehicleRegistrationRegistrationOfficeCosts';

    /**
     * @param list<VehicleRegistrationCostBreakdownItem> $items Individual cost breakdown items.
     * @param VehicleRegistrationCostBreakdownItem|null  $total Total summary item.
     * @param string|null                                $note  Optional note for the registration office costs.
     */
    public function __construct(
        public array $items,
        public ?VehicleRegistrationCostBreakdownItem $total,
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
            items: array_map(
                static fn (array $item): VehicleRegistrationCostBreakdownItem => VehicleRegistrationCostBreakdownItem::fromArray($item),
                Hydrator::requireArrayList($data, 'items', self::CONTEXT),
            ),
            total: ($total = Hydrator::optionalArray($data, 'total', self::CONTEXT)) !== null
                ? VehicleRegistrationCostBreakdownItem::fromArray($total)
                : null,
            note: Hydrator::optionalString($data, 'note', self::CONTEXT),
        );
    }
}
