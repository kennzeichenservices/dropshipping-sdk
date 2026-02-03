<?php

declare(strict_types=1);

namespace Dropshipping\DTO;

use Dropshipping\Enums\ProductType;

/**
 * Data transfer object representing the customization for a license plate order item.
 */
final readonly class LicensePlateItemCustomization implements ItemCustomizationInterface
{
    public ProductType $productType;

    /**
     * Create a new LicensePlateItemCustomization instance.
     */
    public function __construct(
        public LicensePlateNumberComponentsInterface $licensePlateNumberComponents,
        public ?int $seasonStartMonth = null,
        public ?int $seasonEndMonth = null,
    ) {
        $this->productType = ProductType::LicensePlate;
    }

    /**
     * Convert the customization to an associative array, excluding null values.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'productType' => $this->productType->value,
            'licensePlateNumberComponents' => $this->licensePlateNumberComponents->toArray(),
            'seasonStartMonth' => $this->seasonStartMonth,
            'seasonEndMonth' => $this->seasonEndMonth,
        ], static fn (mixed $value): bool => $value !== null);
    }
}
