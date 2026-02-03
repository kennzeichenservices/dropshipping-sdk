<?php

declare(strict_types=1);

namespace Dropshipping\Tests\Integration;

use Dropshipping\Client\ApiClient;
use Dropshipping\Configuration\DropshippingConfig;
use Dropshipping\DTO\Address;
use Dropshipping\DTO\EuroLicensePlateNumberComponents;
use Dropshipping\DTO\LicensePlateItemCustomization;
use Dropshipping\DTO\OrderItem;
use Dropshipping\DTO\Requests\EmissionStickerOrderRequest;
use Dropshipping\DTO\Requests\OrderCreationRequest;
use Dropshipping\DTO\Requests\ReshippedOrderRequest;
use Dropshipping\DTO\Responses\EmissionStickerOrderResponse;
use Dropshipping\DTO\Responses\OrderCreationResponse;
use Dropshipping\Enums\Gender;
use Dropshipping\Exceptions\ApiException;
use GuzzleHttp\Client;
use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\TestCase;

final class OrdersIntegrationTest extends TestCase
{
    private static ?ApiClient $client = null;

    public static function setUpBeforeClass(): void
    {
        $host = getenv('DROPSHIPPING_API_HOST');
        $clientId = getenv('DROPSHIPPING_CLIENT_ID');
        $username = getenv('DROPSHIPPING_USERNAME');
        $password = getenv('DROPSHIPPING_PASSWORD');

        if (!$host || !$clientId || !$username || !$password) {
            static::markTestSkipped('Integration test env vars not set');
        }

        $config = new DropshippingConfig($host, (int) $clientId, $username, $password);
        $psr17 = new Psr17Factory();

        self::$client = new ApiClient($config, new Client(), $psr17, $psr17);
    }

    public function test_create_order(): void
    {
        $address = new Address(
            firstName: 'Test',
            lastName: 'User',
            gender: Gender::Unspecified,
            streetName: 'Teststraße',
            houseNumber: '1',
            zipCode: '12345',
            cityName: 'Berlin',
            countryCode: 'DE',
        );

        $components = new EuroLicensePlateNumberComponents('B', 'AB', '123');
        $item = new OrderItem(
            productVariantId: 1,
            name: 'Kennzeichen',
            sku: 'LP-001',
            quantity: 2,
            customization: new LicensePlateItemCustomization($components),
        );

        $request = new OrderCreationRequest(
            externalId: 'integration-test-' . uniqid(),
            email: 'test@example.com',
            deliveryAddress: $address,
            invoiceAddress: $address,
            items: [$item],
        );

        $response = self::$client->orders->create($request);

        self::assertInstanceOf(OrderCreationResponse::class, $response);
        self::assertGreaterThan(0, $response->id);
    }

    public function test_create_emission_sticker_order(): void
    {
        $address = new Address(
            firstName: 'Test',
            lastName: 'User',
            gender: Gender::Unspecified,
            streetName: 'Teststraße',
            houseNumber: '1',
            zipCode: '12345',
            cityName: 'Berlin',
            countryCode: 'DE',
        );

        $components = new EuroLicensePlateNumberComponents('BO', 'CD', '123');

        $tmpFile = tempnam(sys_get_temp_dir(), 'test') . '.pdf';
        file_put_contents($tmpFile, '%PDF-1.4 fake');

        try {
            $request = new EmissionStickerOrderRequest(
                externalId: 'integration-test-emission-' . uniqid(),
                email: 'test@example.com',
                deliveryAddress: $address,
                invoiceAddress: $address,
                licensePlateNumberComponents: $components,
                electric: false,
                emissionKeyNumber: '35A0',
                filePaths: [$tmpFile],
            );

            $response = self::$client->orders->createEmissionStickerOrder($request);

            self::assertInstanceOf(EmissionStickerOrderResponse::class, $response);
            self::assertGreaterThan(0, $response->id);
            self::assertGreaterThan(0, $response->deliveryId);
        } finally {
            unlink($tmpFile);
        }
    }

    public function test_create_reshipped_order_rejects_non_returned_delivery(): void
    {
        // A reshipped order requires a delivery that has been returned.
        // Since we cannot trigger a return via the API, we verify that
        // the API correctly rejects a reshipment for a non-returned delivery.
        $address = new Address(
            firstName: 'Test',
            lastName: 'User',
            gender: Gender::Unspecified,
            streetName: 'Teststraße',
            houseNumber: '1',
            zipCode: '12345',
            cityName: 'Berlin',
            countryCode: 'DE',
        );

        $components = new EuroLicensePlateNumberComponents('B', 'AB', '123');
        $item = new OrderItem(
            productVariantId: 1,
            name: 'Kennzeichen',
            sku: 'LP-001',
            quantity: 2,
            customization: new LicensePlateItemCustomization($components),
        );

        $orderResponse = self::$client->orders->create(new OrderCreationRequest(
            externalId: 'integration-test-reship-base-' . uniqid(),
            email: 'test@example.com',
            deliveryAddress: $address,
            invoiceAddress: $address,
            items: [$item],
        ));

        $deliveryId = $orderResponse->deliveries[0]->id;

        $request = new ReshippedOrderRequest(
            externalId: 'integration-test-reship-' . uniqid(),
            returnedDeliveryId: $deliveryId,
            deliveryAddress: $address,
            invoiceAddress: $address,
        );

        $this->expectException(ApiException::class);

        self::$client->orders->createReshippedOrder($request);
    }
}
