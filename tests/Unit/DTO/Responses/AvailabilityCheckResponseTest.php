<?php

declare(strict_types=1);

namespace Dropshipping\Tests\Unit\DTO\Responses;

use Dropshipping\DTO\Responses\AvailabilityCheckResponse;
use PHPUnit\Framework\TestCase;

final class AvailabilityCheckResponseTest extends TestCase
{
    public function test_fromArray_parses_available_numbers(): void
    {
        $data = [
            'availableLicensePlateNumbers' => [
                ['city' => 'B', 'middle' => 'AB', 'end' => '123'],
                ['city' => 'B', 'middle' => 'CD', 'end' => '456'],
            ],
        ];

        $response = AvailabilityCheckResponse::fromArray($data);

        self::assertCount(2, $response->availableLicensePlateNumbers);
        self::assertSame('AB', $response->availableLicensePlateNumbers[0]->middle);
        self::assertSame('CD', $response->availableLicensePlateNumbers[1]->middle);
    }

    public function test_fromArray_handles_empty_results(): void
    {
        $response = AvailabilityCheckResponse::fromArray([]);

        self::assertCount(0, $response->availableLicensePlateNumbers);
    }
}
