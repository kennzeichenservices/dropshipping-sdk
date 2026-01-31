<?php

declare(strict_types=1);

namespace Dropshipping\DTO\Responses;

final readonly class Delivery
{
    /**
     * @param list<DeliveryItem> $items
     */
    public function __construct(
        public int $id,
        public array $items = [],
    ) {
    }

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            items: array_map(
                static fn (array $item): DeliveryItem => DeliveryItem::fromArray($item),
                $data['items'] ?? [],
            ),
        );
    }
}
