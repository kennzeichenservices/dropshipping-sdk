<?php

declare(strict_types=1);

namespace Dropshipping\DTO\Responses;

use Dropshipping\Support\Hydrator;

/**
 * Response DTO for order creation.
 *
 * Contains the order ID and the list of deliveries associated with the order.
 */
final readonly class OrderCreationResponse
{
    private const CONTEXT = 'OrderCreationResponse';

    /**
     * Create a new order creation response instance.
     *
     * @param int            $id           The order ID.
     * @param list<Delivery> $deliveries   The deliveries belonging to this order.
     * @param string         $costNetValue Order net cost value. Decimal separator: .
     */
    public function __construct(
        public int $id,
        public array $deliveries,
        public ?string $costNetValue,
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
            deliveries: array_map(
                static fn (array $delivery): Delivery => Delivery::fromArray($delivery),
                Hydrator::optionalArrayList($data, 'deliveries', self::CONTEXT),
            ),
            costNetValue: Hydrator::optionalString($data, 'costNetValue', self::CONTEXT),
        );
    }
}
