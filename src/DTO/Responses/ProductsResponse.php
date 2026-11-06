<?php

declare(strict_types=1);

namespace Dropshipping\DTO\Responses;

use Dropshipping\Support\Hydrator;

/**
 * Response DTO for the product catalog listing.
 *
 * Carries every {@see Product} the client may order, with the net costs that
 * currently apply to it.
 */
final readonly class ProductsResponse
{
    private const CONTEXT = 'ProductsResponse';

    /**
     * @param list<Product> $products The catalog entries.
     */
    public function __construct(
        public array $products,
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
            products: array_map(
                static fn (array $item): Product => Product::fromArray($item),
                Hydrator::requireArrayList($data, 'products', self::CONTEXT),
            ),
        );
    }

    /**
     * Find the catalog entry for a product variant ID, for resolving the ID an
     * order item needs before sending it.
     *
     * Returns the first match. The API is expected to publish one entry per
     * variant, but nothing in the spec guarantees it, so this does not assume
     * uniqueness.
     */
    public function findByVariantId(int $productVariantId): ?Product
    {
        foreach ($this->products as $product) {
            if ($product->productVariantId === $productVariantId) {
                return $product;
            }
        }

        return null;
    }

    /**
     * All catalog entries of one product type, e.g. `LICENSE_PLATE`.
     *
     * The comparison is exact and case sensitive, matching the values the API
     * publishes.
     *
     * @return list<Product>
     */
    public function ofType(string $productType): array
    {
        return array_values(array_filter(
            $this->products,
            static fn (Product $product): bool => $product->productType === $productType,
        ));
    }
}
