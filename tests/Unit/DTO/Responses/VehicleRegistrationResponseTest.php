<?php

declare(strict_types=1);

namespace Dropshipping\Tests\Unit\DTO\Responses;

use Dropshipping\DTO\Responses\VehicleRegistrationResponse;
use Dropshipping\Exceptions\DropshippingException;
use PHPUnit\Framework\TestCase;

final class VehicleRegistrationResponseTest extends TestCase
{
    public function test_fromArray_maps_all_fields(): void
    {
        $response = VehicleRegistrationResponse::fromArray([
            'order' => ['id' => 4711],
        ]);

        self::assertSame(4711, $response->orderId);
    }

    /**
     * The 2.4.0 response dropped identityVerificationVendorId and customerInputFormUrl.
     * Older payloads still carrying them must map, not blow up.
     */
    public function test_fromArray_ignores_fields_removed_in_2_4_0(): void
    {
        $response = VehicleRegistrationResponse::fromArray([
            'order' => ['id' => 4711],
            'identityVerificationVendorId' => 7,
            'customerInputFormUrl' => 'https://example.com/forms/abc123',
        ]);

        self::assertSame(4711, $response->orderId);
    }

    public function test_fromArray_rejects_a_response_without_an_order(): void
    {
        $this->expectException(DropshippingException::class);

        VehicleRegistrationResponse::fromArray([]);
    }
}
