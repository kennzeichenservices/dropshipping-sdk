<?php

declare(strict_types=1);

namespace Dropshipping\DTO\Responses;

use Dropshipping\Support\Hydrator;

/**
 * Response DTO for a vehicle deregistration request.
 *
 * Contains the order ID created as a result of the deregistration.
 * Unlike {@see LicensePlateReservationResponse}, this response
 * does not include a cost net value.
 */
final readonly class VehicleDeregistrationResponse
{
    private const CONTEXT = 'VehicleDeregistrationResponse';

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
            orderId: Hydrator::requireInt(
                Hydrator::requireArray($data, 'order', self::CONTEXT),
                'id',
                self::CONTEXT . '.order',
            ),
        );
    }
}
