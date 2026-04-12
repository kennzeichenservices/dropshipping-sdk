<?php

declare(strict_types=1);

namespace Dropshipping\Tests\Unit\DTO\Responses;

use Dropshipping\DTO\Responses\VehicleDeregistrationResponse;
use PHPUnit\Framework\TestCase;

final class VehicleDeregistrationResponseTest extends TestCase
{
    public function test_fromArray_parses_order_id(): void
    {
        $response = VehicleDeregistrationResponse::fromArray([
            'order' => ['id' => 123],
        ]);

        self::assertSame(123, $response->orderId);
    }
}
