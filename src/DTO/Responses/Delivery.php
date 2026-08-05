<?php

declare(strict_types=1);

namespace Dropshipping\DTO\Responses;

/**
 * Represents a delivery within an order.
 *
 * Contains a unique identifier and a list of delivery items.
 */
final readonly class Delivery
{
    /**
     * Create a new delivery instance.
     *
     * @param int              $id    The delivery ID.
     * @param list<DeliveryItem> $items The items included in this delivery.
     */
    public function __construct(
        public int $id,
        public array $items = [],
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
            id: $data['id'],
            items: array_values(array_map(
                static fn (array $item): DeliveryItem => DeliveryItem::fromArray($item),
                $data['items'] ?? [],
            )),
        );
    }
}
