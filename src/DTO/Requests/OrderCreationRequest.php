<?php

declare(strict_types=1);

namespace Dropshipping\DTO\Requests;

use Dropshipping\DTO\Address;
use Dropshipping\DTO\OrderItem;

/**
 * Request DTO for creating a new dropshipping order.
 *
 * Contains the external ID, customer email, delivery and invoice addresses,
 * order items, and an optional shipper name.
 */
final readonly class OrderCreationRequest
{
    /**
     * @param string          $externalId      External order identifier.
     * @param string          $email           Customer email address.
     * @param Address         $deliveryAddress Delivery address for the order.
     * @param Address         $invoiceAddress  Invoice address for the order.
     * @param list<OrderItem> $items           List of order items.
     * @param string|null     $shipperName     Optional shipper name.
     */
    public function __construct(
        public string $externalId,
        public string $email,
        public Address $deliveryAddress,
        public Address $invoiceAddress,
        public array $items,
        public ?string $shipperName = null,
    ) {
    }

    /**
     * Convert the order request to an associative array, excluding null values.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $data = array_filter([
            'shipperName' => $this->shipperName,
            'externalId' => $this->externalId,
            'email' => $this->email,
            'deliveryAddress' => $this->deliveryAddress->toArray(),
            'invoiceAddress' => $this->invoiceAddress->toArray(),
        ], static fn (mixed $value): bool => $value !== null);

        $data['items'] = array_map(
            static fn (OrderItem $item): array => $item->toArray(),
            $this->items,
        );

        return $data;
    }
}
