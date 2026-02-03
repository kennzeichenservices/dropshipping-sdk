<?php

declare(strict_types=1);

namespace Dropshipping\DTO\Responses;

/**
 * Response DTO for a license plate reservation request.
 *
 * Contains the order ID created as a result of the reservation.
 */
final readonly class LicensePlateReservationResponse
{
    /**
     * Create a new license plate reservation response instance.
     *
     * @param int $orderId The ID of the created order.
     */
    public function __construct(
        public int $orderId,
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
            orderId: $data['order']['id'],
        );
    }
}
