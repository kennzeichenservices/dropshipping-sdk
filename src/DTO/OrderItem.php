<?php

declare(strict_types=1);

namespace Dropshipping\DTO;

use Dropshipping\Support\Validator;

/**
 * Data transfer object representing a single item in a dropshipping order.
 */
final readonly class OrderItem
{
    /**
     * Create a new OrderItem instance.
     */
    public function __construct(
        public int $productVariantId,
        public string $name,
        public string $sku,
        public int $quantity,
        public ItemCustomizationInterface $customization,
    ) {
        Validator::requireStringLength($name, 'name', 1, 100);
        Validator::requireStringLength($sku, 'sku', 1, 20);
        Validator::requireIntRange($quantity, 'quantity', 1, PHP_INT_MAX);
    }

    /**
     * Convert the order item to an associative array.
     *
     * @return array<string, mixed>
     */
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
