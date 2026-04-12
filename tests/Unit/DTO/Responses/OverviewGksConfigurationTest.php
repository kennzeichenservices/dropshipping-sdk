<?php

declare(strict_types=1);

namespace Dropshipping\Tests\Unit\DTO\Responses;

use Dropshipping\DTO\Responses\OverviewGksConfiguration;
use PHPUnit\Framework\TestCase;

final class OverviewGksConfigurationTest extends TestCase
{
    public function test_fromArray_creates_instance(): void
    {
        $overview = OverviewGksConfiguration::fromArray([
            'id' => '550e8400-e29b-41d4-a716-446655440000',
            'name' => 'My GKS Config',
        ]);

        self::assertSame('550e8400-e29b-41d4-a716-446655440000', $overview->id);
        self::assertSame('My GKS Config', $overview->name);
    }
}
