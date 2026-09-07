<?php

declare(strict_types=1);

namespace Dropshipping\Tests\Unit\DTO\Responses;

use Dropshipping\DTO\Responses\EnabledGksClientVersionsResponse;
use Dropshipping\Exceptions\DropshippingException;
use PHPUnit\Framework\TestCase;

final class EnabledGksClientVersionsResponseTest extends TestCase
{
    public function test_fromArray_creates_instance(): void
    {
        $response = EnabledGksClientVersionsResponse::fromArray([
            'gksClientVersions' => [
                ['versionNumber' => '2.0'],
                ['versionNumber' => '3.0'],
            ],
        ]);

        self::assertCount(2, $response->gksClientVersions);
        self::assertSame('2.0', $response->gksClientVersions[0]->versionNumber);
        self::assertSame('3.0', $response->gksClientVersions[1]->versionNumber);
    }

    public function test_versionNumbers_lists_the_numbers(): void
    {
        $response = EnabledGksClientVersionsResponse::fromArray([
            'gksClientVersions' => [
                ['versionNumber' => '2.0'],
                ['versionNumber' => '3.0'],
            ],
        ]);

        self::assertSame(['2.0', '3.0'], $response->versionNumbers());
    }

    public function test_fromArray_accepts_empty_list(): void
    {
        $response = EnabledGksClientVersionsResponse::fromArray(['gksClientVersions' => []]);

        self::assertSame([], $response->gksClientVersions);
        self::assertSame([], $response->versionNumbers());
    }

    public function test_fromArray_requires_the_list(): void
    {
        $this->expectException(DropshippingException::class);

        EnabledGksClientVersionsResponse::fromArray([]);
    }
}
