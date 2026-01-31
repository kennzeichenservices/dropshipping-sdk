<?php

declare(strict_types=1);

namespace Dropshipping\Tests\Unit\Http;

use Dropshipping\Exceptions\ApiException;
use Dropshipping\Http\ResponseMapper;
use Dropshipping\Serialization\ArrayMapper;
use Nyholm\Psr7\Response;
use PHPUnit\Framework\TestCase;

final class ResponseMapperTest extends TestCase
{
    private ResponseMapper $mapper;

    protected function setUp(): void
    {
        $this->mapper = new ResponseMapper(new ArrayMapper());
    }

    public function test_mapResponse_decodes_json_body(): void
    {
        $response = new Response(200, [], '{"id":1,"name":"test"}');

        $result = $this->mapper->mapResponse($response);

        self::assertSame(['id' => 1, 'name' => 'test'], $result);
    }

    public function test_mapResponse_throws_on_unexpected_status(): void
    {
        $response = new Response(400, [], '{"error":"Bad request"}');

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('Bad request');

        try {
            $this->mapper->mapResponse($response);
        } catch (ApiException $e) {
            self::assertSame(400, $e->getStatusCode());
            throw $e;
        }
    }

    public function test_mapResponse_includes_trace_id(): void
    {
        $response = new Response(500, ['X-Trace-Id' => 'abc-123'], '{"error":"Server error"}');

        try {
            $this->mapper->mapResponse($response);
            self::fail('Expected ApiException');
        } catch (ApiException $e) {
            self::assertSame('abc-123', $e->getTraceId());
            self::assertSame(500, $e->getStatusCode());
        }
    }

    public function test_mapResponse_returns_empty_array_on_empty_body(): void
    {
        $response = new Response(200, [], '');

        $result = $this->mapper->mapResponse($response);

        self::assertSame([], $result);
    }

    public function test_mapResponse_accepts_custom_expected_status_codes(): void
    {
        $response = new Response(201, [], '{"id":5}');

        $result = $this->mapper->mapResponse($response, [201]);

        self::assertSame(['id' => 5], $result);
    }

    public function test_mapResponse_error_without_body_uses_default_message(): void
    {
        $response = new Response(404, [], '');

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('API request failed');

        $this->mapper->mapResponse($response);
    }
}
