<?php

declare(strict_types=1);

namespace Dropshipping\DTO\Responses;

use Dropshipping\Support\Hydrator;

/**
 * Represents a delivery within an order.
 *
 * Contains a unique identifier and a list of delivery items.
 */
final readonly class Delivery
{
    private const CONTEXT = 'Delivery';

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
            id: Hydrator::requireInt($data, 'id', self::CONTEXT),
            items: array_map(
                static fn (array $item): DeliveryItem => DeliveryItem::fromArray($item),
                Hydrator::optionalArrayList($data, 'items', self::CONTEXT),
            ),
        );
    }
}
