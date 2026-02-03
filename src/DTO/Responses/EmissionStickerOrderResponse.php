<?php

declare(strict_types=1);

namespace Dropshipping\DTO\Responses;

/**
 * Response DTO for emission sticker order creation.
 *
 * Contains the order ID and the associated delivery ID.
 */
final readonly class EmissionStickerOrderResponse
{
    /**
     * Create a new emission sticker order response instance.
     *
     * @param int $id         The order ID.
     * @param int $deliveryId The delivery ID.
     */
    public function __construct(
        public int $id,
        public int $deliveryId,
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
            deliveryId: $data['delivery']['id'],
        );
    }
}
