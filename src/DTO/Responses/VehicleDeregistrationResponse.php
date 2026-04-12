<?php

declare(strict_types=1);

namespace Dropshipping\DTO\Responses;

/**
 * Response DTO for a vehicle deregistration request.
 *
 * Contains the order ID created as a result of the deregistration.
 * Unlike {@see LicensePlateReservationResponse}, this response
 * does not include a cost net value.
 */
final readonly class VehicleDeregistrationResponse
{
    /**
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
     *
     * @return self
     */
    public static function fromArray(array $data): self
    {
        return new self(
            orderId: $data['order']['id'],
        );
    }
}
