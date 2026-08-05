<?php

declare(strict_types=1);

namespace Dropshipping\DTO\Responses;

use Dropshipping\Support\Hydrator;

/**
 * Represents an item within a delivery.
 *
 * References the original order item by its index position.
 */
final readonly class DeliveryItem
{
    private const CONTEXT = 'DeliveryItem';

    /**
     * Create a new delivery item instance.
     *
     * @param int $orderItemIndex The index of the corresponding order item.
     */
    public function __construct(
        public int $orderItemIndex,
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
            orderItemIndex: Hydrator::requireInt($data, 'orderItemIndex', self::CONTEXT),
        );
    }
}
