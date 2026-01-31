<?php

declare(strict_types=1);

namespace Dropshipping\DTO\Responses;

final readonly class DeliveryItem
{
    public function __construct(
        public int $orderItemIndex,
    ) {
    }

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            orderItemIndex: $data['orderItemIndex'],
        );
    }
}
