<?php

declare(strict_types=1);

namespace Dropshipping\Tests\Unit\DTO\Requests;

use Dropshipping\DTO\Address;
use Dropshipping\DTO\EuroLicensePlateNumberComponents;
use Dropshipping\DTO\LicensePlateItemCustomization;
use Dropshipping\DTO\OrderItem;
use Dropshipping\DTO\Requests\OrderCreationRequest;
use Dropshipping\Enums\Gender;
use PHPUnit\Framework\TestCase;

final class OrderCreationRequestTest extends TestCase
{
    private function createAddress(): Address
    {
        return new Address('Max', 'Mustermann', Gender::Male, 'Str', '1', '12345', 'Berlin', 'DE');
    }

    private function createOrderItem(): OrderItem
    {
        $components = new EuroLicensePlateNumberComponents('B', 'AB', '123');
        return new OrderItem(1, 'Kennzeichen', 'LP-001', 2, new LicensePlateItemCustomization($components));
    }

    public function test_toArray_includes_all_fields(): void
    {
        $request = new OrderCreationRequest(
            externalId: 'ext-1',
            email: 'test@example.com',
            deliveryAddress: $this->createAddress(),
            invoiceAddress: $this->createAddress(),
            items: [$this->createOrderItem()],
            shipperName: 'DHL',
        );

        $array = $request->toArray();

        self::assertSame('ext-1', $array['externalId']);
        self::assertSame('test@example.com', $array['email']);
        self::assertSame('DHL', $array['shipperName']);
        self::assertArrayHasKey('deliveryAddress', $array);
        self::assertArrayHasKey('invoiceAddress', $array);
        self::assertCount(1, $array['items']);
        self::assertSame(1, $array['items'][0]['productVariantId']);
    }

    public function test_toArray_excludes_null_shipperName(): void
    {
        $request = new OrderCreationRequest(
            externalId: 'ext-1',
            email: 'test@example.com',
            deliveryAddress: $this->createAddress(),
            invoiceAddress: $this->createAddress(),
            items: [$this->createOrderItem()],
        );

        $array = $request->toArray();

        self::assertArrayNotHasKey('shipperName', $array);
    }
}
