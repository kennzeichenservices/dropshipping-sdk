<?php

declare(strict_types=1);

namespace Dropshipping\Tests\Unit\DTO\Responses;

use Dropshipping\DTO\Responses\VehicleRegistrationResponse;
use PHPUnit\Framework\TestCase;

final class VehicleRegistrationResponseTest extends TestCase
{
    public function test_fromArray_maps_all_fields(): void
    {
        $response = VehicleRegistrationResponse::fromArray([
            'order' => ['id' => 4711],
            'identityVerificationVendorId' => 7,
            'customerInputFormUrl' => 'https://example.com/forms/abc123',
        ]);

        self::assertSame(4711, $response->orderId);
        self::assertSame(7, $response->identityVerificationVendorId);
        self::assertSame('https://example.com/forms/abc123', $response->customerInputFormUrl);
    }
}
