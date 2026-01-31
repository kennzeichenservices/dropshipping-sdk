<?php

declare(strict_types=1);

namespace Dropshipping\DTO\Requests;

use Dropshipping\DTO\Address;

final readonly class ReshippedOrderRequest
{
    public function __construct(
        public string $externalId,
        public int $returnedDeliveryId,
        public Address $deliveryAddress,
        public Address $invoiceAddress,
    ) {
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'externalId' => $this->externalId,
            'returnedDeliveryId' => $this->returnedDeliveryId,
            'deliveryAddress' => $this->deliveryAddress->toArray(),
            'invoiceAddress' => $this->invoiceAddress->toArray(),
        ];
    }
}
