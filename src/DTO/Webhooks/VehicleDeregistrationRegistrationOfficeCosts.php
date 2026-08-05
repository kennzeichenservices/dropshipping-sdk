<?php

declare(strict_types=1);

namespace Dropshipping\DTO\Webhooks;

use Dropshipping\Support\Hydrator;

/**
 * Represents the registration office costs in a vehicle deregistration webhook event.
 *
 * Contains a list of individual cost breakdown items, an optional total summary item,
 * and an optional note.
 */
final readonly class VehicleDeregistrationRegistrationOfficeCosts
{
    private const CONTEXT = 'VehicleDeregistrationRegistrationOfficeCosts';

    /**
     * @param list<VehicleDeregistrationCostBreakdownItem> $items Individual cost breakdown items.
     * @param VehicleDeregistrationCostBreakdownItem|null  $total Total summary item.
     * @param string|null                                  $note  Optional note for the registration office costs.
     */
    public function __construct(
        public array $items,
        public ?VehicleDeregistrationCostBreakdownItem $total,
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
                static fn (array $item): VehicleDeregistrationCostBreakdownItem => VehicleDeregistrationCostBreakdownItem::fromArray($item),
                Hydrator::requireArrayList($data, 'items', self::CONTEXT),
            ),
            total: ($total = Hydrator::optionalArray($data, 'total', self::CONTEXT)) !== null
                ? VehicleDeregistrationCostBreakdownItem::fromArray($total)
                : null,
            note: Hydrator::optionalString($data, 'note', self::CONTEXT),
        );
    }
}
