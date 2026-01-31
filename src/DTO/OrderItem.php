<?php

declare(strict_types=1);

namespace Dropshipping\DTO;

final readonly class OrderItem
{
    public function __construct(
        public int $productVariantId,
        public string $name,
        public string $sku,
        public int $quantity,
        public ItemCustomizationInterface $customization,
    ) {
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'productVariantId' => $this->productVariantId,
            'name' => $this->name,
            'sku' => $this->sku,
            'quantity' => $this->quantity,
            'customization' => $this->customization->toArray(),
        ];
    }
}
