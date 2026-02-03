<?php

declare(strict_types=1);

namespace Dropshipping\DTO;

use Dropshipping\Enums\ProductType;

/**
 * Data transfer object representing the customization for a non-license-plate order item.
 */
final readonly class OtherItemCustomization implements ItemCustomizationInterface
{
    public ProductType $productType;

    /**
     * Create a new OtherItemCustomization instance.
     */
    public function __construct()
    {
        $this->productType = ProductType::Other;
    }

    /**
     * Convert the customization to an associative array.
     *
     * @return array<string, string>
     */
    public function toArray(): array
    {
        return [
            'productType' => $this->productType->value,
        ];
    }
}
