<?php

declare(strict_types=1);

namespace Dropshipping\DTO\Responses;

use Dropshipping\Support\Hydrator;

/**
 * Response DTO for a single orderable product and its current net costs.
 *
 * One entry of the catalog published by API 2.6.0, the same content as the
 * product catalog CSV export of the dropshipping self service.
 *
 * Every cost is a decimal string with a period as the separator and stays a
 * string on purpose: these are billed cent amounts, and a float cannot hold
 * them exactly. Convert them where you need arithmetic, not here.
 */
final readonly class Product
{
    private const CONTEXT = 'Product';

    /**
     * @param string      $productType              Product type, e.g. `LICENSE_PLATE` or `EMISSION_STICKER`. A plain string rather than {@see \Dropshipping\Enums\ProductType}: the catalog publishes types that enum does not model, and the spec declares the field as a string.
     * @param string      $productVariantName       Human readable variant name, e.g. "Euro-Kennzeichen 520 x 110 mm".
     * @param int         $productVariantId         ID of the variant, as used for `productVariantId` in order items.
     * @param int         $supplierId               ID of the supplier that ships this variant.
     * @param string      $costItem1                Net cost of the first item of the variant.
     * @param string      $costItem2                Net cost of the second item of the variant.
     * @param string      $costItem3                Net cost of the third item of the variant.
     * @param string      $costItem4                Net cost of the fourth item of the variant.
     * @param string      $shippingCost             Net shipping cost charged by the supplier.
     * @param string|null $utschSku                 SKU at the supplier UTSCH, null when UTSCH does not supply this product.
     * @param int|null    $licensePlateNumberLength Maximum license plate number length, null when the product is not a license plate.
     */
    public function __construct(
        public string $productType,
        public string $productVariantName,
        public int $productVariantId,
        public int $supplierId,
        public string $costItem1,
        public string $costItem2,
        public string $costItem3,
        public string $costItem4,
        public string $shippingCost,
        public ?string $utschSku = null,
        public ?int $licensePlateNumberLength = null,
    ) {
    }

    /**
     * Create an instance from a raw API response array.
     *
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            productType: Hydrator::requireString($data, 'productType', self::CONTEXT),
            productVariantName: Hydrator::requireString($data, 'productVariantName', self::CONTEXT),
            productVariantId: Hydrator::requireInt($data, 'productVariantId', self::CONTEXT),
            supplierId: Hydrator::requireInt($data, 'supplierId', self::CONTEXT),
            costItem1: Hydrator::requireString($data, 'costItem1', self::CONTEXT),
            costItem2: Hydrator::requireString($data, 'costItem2', self::CONTEXT),
            costItem3: Hydrator::requireString($data, 'costItem3', self::CONTEXT),
            costItem4: Hydrator::requireString($data, 'costItem4', self::CONTEXT),
            shippingCost: Hydrator::requireString($data, 'shippingCost', self::CONTEXT),
            utschSku: Hydrator::optionalString($data, 'utschSku', self::CONTEXT),
            licensePlateNumberLength: Hydrator::optionalInt($data, 'licensePlateNumberLength', self::CONTEXT),
        );
    }

    /**
     * Net cost of the n-th item of this variant, 1 through 4.
     *
     * The API publishes the four costs as separate fields because the price per
     * item drops with quantity. Reaching for the fourth cost on a larger order
     * is the caller's job; this only resolves the position.
     *
     * @param int $position Item position, 1 to 4.
     *
     * @throws \Dropshipping\Exceptions\DropshippingException When the position is outside 1 to 4.
     */
    public function costForItem(int $position): string
    {
        return match ($position) {
            1 => $this->costItem1,
            2 => $this->costItem2,
            3 => $this->costItem3,
            4 => $this->costItem4,
            default => throw new \Dropshipping\Exceptions\DropshippingException(sprintf(
                'Item position must be between 1 and 4, got %d.',
                $position,
            )),
        };
    }

    /**
     * Whether this product is a license plate, judged by the field the API ties to it.
     */
    public function isLicensePlate(): bool
    {
        return $this->licensePlateNumberLength !== null;
    }
}
