<?php

declare(strict_types=1);

namespace Dropshipping\DTO\Requests;

use Dropshipping\DTO\Address;
use Dropshipping\Support\Validator;

/**
 * Request DTO for creating a reshipped order for a returned delivery.
 *
 * Contains the external ID, the returned delivery ID, and the
 * delivery and invoice addresses for the new shipment.
 */
final readonly class ReshippedOrderRequest
{
    /**
     * @param string  $externalId        External order identifier.
     * @param int     $returnedDeliveryId ID of the returned delivery.
     * @param Address $deliveryAddress    Delivery address for the reshipped order.
     * @param Address $invoiceAddress     Invoice address for the reshipped order.
     */
    public function __construct(
        public string $externalId,
        public int $returnedDeliveryId,
        public Address $deliveryAddress,
        public Address $invoiceAddress,
    ) {
        Validator::requireStringLength($externalId, 'externalId', 1, 100);
    }

    /**
     * Convert the reshipped order request to an associative array.
     *
     * @return array<string, mixed>
     */
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
