<?php

declare(strict_types=1);

namespace Dropshipping\DTO\Responses;

final readonly class OrderCreationResponse
{
    /**
     * @param list<Delivery> $deliveries
     */
    public function __construct(
        public int $id,
        public array $deliveries,
    ) {
    }

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            deliveries: array_map(
                static fn (array $delivery): Delivery => Delivery::fromArray($delivery),
                $data['deliveries'] ?? [],
            ),
        );
    }
}
