<?php

declare(strict_types=1);

namespace Dropshipping\DTO\Webhooks;

/**
 * Represents the registration office costs in a vehicle deregistration webhook event.
 *
 * Contains a list of individual cost breakdown items, an optional total summary item,
 * and an optional note.
 */
final readonly class VehicleDeregistrationRegistrationOfficeCosts
{
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
            items: array_values(array_map(
                static fn (array $item): VehicleDeregistrationCostBreakdownItem => VehicleDeregistrationCostBreakdownItem::fromArray($item),
                $data['items'],
            )),
            total: isset($data['total']) ? VehicleDeregistrationCostBreakdownItem::fromArray($data['total']) : null,
            note: $data['note'] ?? null,
        );
    }
}
