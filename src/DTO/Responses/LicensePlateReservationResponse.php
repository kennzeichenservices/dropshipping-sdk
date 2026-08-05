<?php

declare(strict_types=1);

namespace Dropshipping\DTO\Responses;

use Dropshipping\Support\Hydrator;

/**
 * Response DTO for a license plate reservation request.
 *
 * Contains the order ID created as a result of the reservation.
 */
final readonly class LicensePlateReservationResponse
{
    private const CONTEXT = 'LicensePlateReservationResponse';

    /**
     * Create a new license plate reservation response instance.
     *
     * @param int    $orderId      The ID of the created order.
     * @param string $costNetValue Order net cost value. Decimal separator: .
     */
    public function __construct(
        public int $orderId,
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
        $order = Hydrator::requireArray($data, 'order', self::CONTEXT);

        return new self(
            orderId: Hydrator::requireInt($order, 'id', self::CONTEXT . '.order'),
            costNetValue: Hydrator::requireString($order, 'costNetValue', self::CONTEXT . '.order'),
        );
    }
}
