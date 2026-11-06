<?php

declare(strict_types=1);

namespace Dropshipping\Tests\Unit\DTO\Responses;

use Dropshipping\DTO\Responses\Product;
use Dropshipping\DTO\Responses\ProductsResponse;
use Dropshipping\Exceptions\DropshippingException;
use PHPUnit\Framework\TestCase;

final class ProductsResponseTest extends TestCase
{
    public function test_fromArray_hydrates_every_field(): void
    {
        $response = ProductsResponse::fromArray([
            'products' => [
                [
                    'productType' => 'LICENSE_PLATE',
                    'productVariantName' => 'Euro-Kennzeichen 520 x 110 mm',
                    'productVariantId' => 2,
                    'utschSku' => 'SKU-123',
                    'supplierId' => 1,
                    'costItem1' => '9.90',
                    'costItem2' => '7.90',
                    'costItem3' => '7.90',
                    'costItem4' => '7.90',
                    'licensePlateNumberLength' => 9,
                    'shippingCost' => '3.45',
                ],
            ],
        ]);

        self::assertCount(1, $response->products);
        $product = $response->products[0];
        self::assertSame('LICENSE_PLATE', $product->productType);
        self::assertSame('Euro-Kennzeichen 520 x 110 mm', $product->productVariantName);
        self::assertSame(2, $product->productVariantId);
        self::assertSame('SKU-123', $product->utschSku);
        self::assertSame(1, $product->supplierId);
        self::assertSame('9.90', $product->costItem1);
        self::assertSame('7.90', $product->costItem4);
        self::assertSame(9, $product->licensePlateNumberLength);
        self::assertSame('3.45', $product->shippingCost);
    }

    /**
     * An emission sticker carries null in both nullable fields, which is the shape
     * the spec's own example shows.
     */
    public function test_fromArray_accepts_null_for_the_nullable_fields(): void
    {
        $product = Product::fromArray($this->emissionStickerPayload());

        self::assertNull($product->utschSku);
        self::assertNull($product->licensePlateNumberLength);
        self::assertFalse($product->isLicensePlate());
    }

    public function test_fromArray_accepts_absent_nullable_fields(): void
    {
        $payload = $this->emissionStickerPayload();
        unset($payload['utschSku'], $payload['licensePlateNumberLength']);

        $product = Product::fromArray($payload);

        self::assertNull($product->utschSku);
        self::assertNull($product->licensePlateNumberLength);
    }

    public function test_isLicensePlate_is_true_when_a_number_length_is_published(): void
    {
        $payload = $this->emissionStickerPayload();
        $payload['licensePlateNumberLength'] = 8;

        self::assertTrue(Product::fromArray($payload)->isLicensePlate());
    }

    /**
     * Costs stay strings. Reading them as floats would round cent amounts that end
     * up on an invoice.
     */
    public function test_costs_are_not_converted_to_floats(): void
    {
        $payload = $this->emissionStickerPayload();
        $payload['costItem1'] = '4.50';

        self::assertSame('4.50', Product::fromArray($payload)->costItem1);
    }

    public function test_costForItem_resolves_each_position(): void
    {
        $payload = $this->emissionStickerPayload();
        $payload['costItem1'] = '1.00';
        $payload['costItem2'] = '2.00';
        $payload['costItem3'] = '3.00';
        $payload['costItem4'] = '4.00';

        $product = Product::fromArray($payload);

        self::assertSame('1.00', $product->costForItem(1));
        self::assertSame('2.00', $product->costForItem(2));
        self::assertSame('3.00', $product->costForItem(3));
        self::assertSame('4.00', $product->costForItem(4));
    }

    public function test_costForItem_rejects_a_position_outside_the_published_range(): void
    {
        $product = Product::fromArray($this->emissionStickerPayload());

        $this->expectException(DropshippingException::class);
        $this->expectExceptionMessage('Item position must be between 1 and 4, got 5.');

        $product->costForItem(5);
    }

    public function test_costForItem_rejects_a_zero_position(): void
    {
        $product = Product::fromArray($this->emissionStickerPayload());

        $this->expectException(DropshippingException::class);

        $product->costForItem(0);
    }

    public function test_fromArray_rejects_a_missing_required_field(): void
    {
        $payload = $this->emissionStickerPayload();
        unset($payload['costItem3']);

        $this->expectException(DropshippingException::class);
        $this->expectExceptionMessage('costItem3');

        Product::fromArray($payload);
    }

    /**
     * A numeric cost would be a silent precision trap, so a retyped field has to
     * surface as an SDK exception rather than a TypeError.
     */
    public function test_fromArray_rejects_a_numeric_cost(): void
    {
        $payload = $this->emissionStickerPayload();
        $payload['costItem1'] = 4.5;

        $this->expectException(DropshippingException::class);
        $this->expectExceptionMessage('costItem1');

        Product::fromArray($payload);
    }

    public function test_fromArray_rejects_a_string_variant_id(): void
    {
        $payload = $this->emissionStickerPayload();
        $payload['productVariantId'] = '6';

        $this->expectException(DropshippingException::class);
        $this->expectExceptionMessage('productVariantId');

        Product::fromArray($payload);
    }

    public function test_fromArray_rejects_an_array_where_a_scalar_belongs(): void
    {
        $payload = $this->emissionStickerPayload();
        $payload['productType'] = ['LICENSE_PLATE'];

        $this->expectException(DropshippingException::class);
        $this->expectExceptionMessage('productType');

        Product::fromArray($payload);
    }

    public function test_fromArray_rejects_a_missing_products_key(): void
    {
        $this->expectException(DropshippingException::class);
        $this->expectExceptionMessage('products');

        ProductsResponse::fromArray([]);
    }

    public function test_fromArray_rejects_a_products_value_that_is_not_a_list_of_objects(): void
    {
        $this->expectException(DropshippingException::class);
        $this->expectExceptionMessage('products');

        ProductsResponse::fromArray(['products' => ['LICENSE_PLATE']]);
    }

    public function test_fromArray_accepts_an_empty_catalog(): void
    {
        $response = ProductsResponse::fromArray(['products' => []]);

        self::assertSame([], $response->products);
        self::assertNull($response->findByVariantId(1));
        self::assertSame([], $response->ofType('LICENSE_PLATE'));
    }

    public function test_findByVariantId_returns_the_matching_entry(): void
    {
        $response = $this->catalog();

        self::assertSame('Feinstaubplakette', $response->findByVariantId(6)?->productVariantName);
        self::assertSame(2, $response->findByVariantId(2)?->productVariantId);
    }

    public function test_findByVariantId_returns_null_for_an_unknown_id(): void
    {
        self::assertNull($this->catalog()->findByVariantId(999));
    }

    /**
     * A variant ID is an int in the payload, so a caller passing one from a string
     * source must not get a loose match on a different entry.
     */
    public function test_findByVariantId_returns_the_first_match_when_ids_repeat(): void
    {
        $first = $this->emissionStickerPayload();
        $first['productVariantName'] = 'First';
        $second = $this->emissionStickerPayload();
        $second['productVariantName'] = 'Second';

        $response = ProductsResponse::fromArray(['products' => [$first, $second]]);

        self::assertSame('First', $response->findByVariantId(6)?->productVariantName);
    }

    public function test_ofType_filters_and_reindexes(): void
    {
        $plates = $this->catalog()->ofType('LICENSE_PLATE');

        self::assertCount(1, $plates);
        self::assertArrayHasKey(0, $plates);
        self::assertSame(2, $plates[0]->productVariantId);
    }

    public function test_ofType_is_case_sensitive(): void
    {
        self::assertSame([], $this->catalog()->ofType('license_plate'));
    }

    /**
     * The catalog publishes types the SDK's ProductType enum does not model, so the
     * field has to stay a plain string instead of being mapped onto that enum.
     */
    public function test_a_type_outside_the_product_type_enum_hydrates_unchanged(): void
    {
        $stickers = $this->catalog()->ofType('EMISSION_STICKER');

        self::assertCount(1, $stickers);
        self::assertSame('EMISSION_STICKER', $stickers[0]->productType);
    }

    private function catalog(): ProductsResponse
    {
        return ProductsResponse::fromArray([
            'products' => [
                $this->emissionStickerPayload(),
                [
                    'productType' => 'LICENSE_PLATE',
                    'productVariantName' => 'Euro-Kennzeichen 520 x 110 mm',
                    'productVariantId' => 2,
                    'utschSku' => 'SKU-123',
                    'supplierId' => 1,
                    'costItem1' => '9.90',
                    'costItem2' => '7.90',
                    'costItem3' => '7.90',
                    'costItem4' => '7.90',
                    'licensePlateNumberLength' => 9,
                    'shippingCost' => '3.45',
                ],
            ],
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function emissionStickerPayload(): array
    {
        return [
            'productType' => 'EMISSION_STICKER',
            'productVariantName' => 'Feinstaubplakette',
            'productVariantId' => 6,
            'utschSku' => null,
            'supplierId' => 2,
            'costItem1' => '4.50',
            'costItem2' => '4.50',
            'costItem3' => '4.50',
            'costItem4' => '4.50',
            'licensePlateNumberLength' => null,
            'shippingCost' => '2.00',
        ];
    }
}
