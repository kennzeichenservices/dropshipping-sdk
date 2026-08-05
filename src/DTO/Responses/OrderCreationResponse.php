<?php

declare(strict_types=1);

namespace Dropshipping\DTO\Responses;

/**
 * Response DTO for order creation.
 *
 * Contains the order ID and the list of deliveries associated with the order.
 */
final readonly class OrderCreationResponse
{
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
            id: $data['id'],
            deliveries: array_values(array_map(
                static fn (array $delivery): Delivery => Delivery::fromArray($delivery),
                $data['deliveries'] ?? [],
            )),
            costNetValue: $data['costNetValue'] ?? null,
        );
    }
}
