<?php

declare(strict_types=1);

namespace Dropshipping\DTO\Responses;

final readonly class EmissionStickerOrderResponse
{
    public function __construct(
        public int $id,
        public int $deliveryId,
    ) {
    }

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            deliveryId: $data['delivery']['id'],
        );
    }
}
