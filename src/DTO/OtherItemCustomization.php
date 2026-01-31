<?php

declare(strict_types=1);

namespace Dropshipping\DTO;

use Dropshipping\Enums\ProductType;

final readonly class OtherItemCustomization implements ItemCustomizationInterface
{
    public ProductType $productType;

    public function __construct()
    {
        $this->productType = ProductType::Other;
    }

    /** @return array<string, string> */
    public function toArray(): array
    {
        return [
            'productType' => $this->productType->value,
        ];
    }
}
