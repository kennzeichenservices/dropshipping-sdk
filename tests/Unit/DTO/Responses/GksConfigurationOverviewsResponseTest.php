<?php

declare(strict_types=1);

namespace Dropshipping\Tests\Unit\DTO\Responses;

use Dropshipping\DTO\Responses\GksConfigurationOverviewsResponse;
use PHPUnit\Framework\TestCase;

final class GksConfigurationOverviewsResponseTest extends TestCase
{
    public function test_fromArray_maps_overview_list(): void
    {
        $response = GksConfigurationOverviewsResponse::fromArray([
            'overviewGksConfigurations' => [
                ['id' => 'uuid-1', 'name' => 'Config A'],
                ['id' => 'uuid-2', 'name' => 'Config B'],
            ],
        ]);

        self::assertCount(2, $response->overviewGksConfigurations);
        self::assertSame('uuid-1', $response->overviewGksConfigurations[0]->id);
        self::assertSame('Config A', $response->overviewGksConfigurations[0]->name);
        self::assertSame('uuid-2', $response->overviewGksConfigurations[1]->id);
    }

    public function test_fromArray_handles_empty_list(): void
    {
        $response = GksConfigurationOverviewsResponse::fromArray([
            'overviewGksConfigurations' => [],
        ]);

        self::assertCount(0, $response->overviewGksConfigurations);
    }
}
