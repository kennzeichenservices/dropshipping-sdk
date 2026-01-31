<?php

declare(strict_types=1);

namespace Dropshipping\DTO\Responses;

final readonly class LicensePlateReservationResponse
{
    public function __construct(
        public int $orderId,
    ) {
    }

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            orderId: $data['order']['id'],
        );
    }
}
