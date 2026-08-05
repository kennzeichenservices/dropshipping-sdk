<?php

declare(strict_types=1);

namespace Dropshipping\DTO\Responses;

use Dropshipping\Support\Hydrator;

/**
 * Response DTO for emission sticker order creation.
 *
 * Contains the order ID and the associated delivery ID.
 */
final readonly class EmissionStickerOrderResponse
{
    private const CONTEXT = 'EmissionStickerOrderResponse';

    /**
     * Create a new emission sticker order response instance.
     *
     * @param int    $id           The order ID.
     * @param int    $deliveryId   The delivery ID.
     * @param string $costNetValue Order net cost value. Decimal separator: .
     */
    public function __construct(
        public int $id,
        public int $deliveryId,
        public string $costNetValue,
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
            deliveryId: Hydrator::requireInt(
                Hydrator::requireArray($data, 'delivery', self::CONTEXT),
                'id',
                self::CONTEXT . '.delivery',
            ),
            costNetValue: Hydrator::requireString($data, 'costNetValue', self::CONTEXT),
        );
    }
}
