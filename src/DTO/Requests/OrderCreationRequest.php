<?php

declare(strict_types=1);

namespace Dropshipping\DTO\Requests;

use Dropshipping\DTO\Address;
use Dropshipping\DTO\OrderItem;

final readonly class OrderCreationRequest
{
    /**
     * @param list<OrderItem> $items
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

    /** @return array<string, mixed> */
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
