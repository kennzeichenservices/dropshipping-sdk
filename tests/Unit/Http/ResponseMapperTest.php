<?php

declare(strict_types=1);

namespace Dropshipping\Tests\Unit\Http;

use Dropshipping\Exceptions\ApiException;
use Dropshipping\Http\ResponseMapper;
use Dropshipping\Serialization\ArrayMapper;
use Nyholm\Psr7\Response;
use PHPUnit\Framework\Attributes\DataProvider;
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

    /**
     * Gateways, CDNs and WAFs answer with HTML, not JSON. Decoding that used to throw a
     * raw JsonException, which is not a DropshippingException — so callers guarding an
     * order submission crashed on exactly the infrastructure failures they meant to catch.
     *
     * @param string $body Non-JSON error body as an intermediary would return it.
     */
    #[DataProvider('nonJsonErrorBodies')]
    public function test_mapResponse_reports_non_json_error_bodies_as_api_exception(string $body): void
    {
        $response = new Response(502, [], $body);

        try {
            $this->mapper->mapResponse($response);
            self::fail('Expected ApiException');
        } catch (ApiException $e) {
            self::assertSame(502, $e->getStatusCode());
            self::assertSame('API request failed', $e->getMessage());
        }
    }

    /**
     * @return array<string, array{string}>
     */
    public static function nonJsonErrorBodies(): array
    {
        return [
            'html error page' => ['<html><head><title>502 Bad Gateway</title></head></html>'],
            'plain text' => ['Service Unavailable'],
            'truncated json' => ['{"error":"Bad requ'],
            // Valid JSON, but decodes to a scalar rather than an array.
            'json string literal' => ['"Gateway Timeout"'],
            'json null literal' => ['null'],
        ];
    }

    /**
     * The API types `error` as a string today, but a structured value used to reach
     * ApiException's string parameter unchecked and fail with a TypeError.
     */
    public function test_mapResponse_falls_back_when_error_field_is_not_a_string(): void
    {
        $response = new Response(422, [], '{"error":{"field":"email","msg":"invalid"}}');

        try {
            $this->mapper->mapResponse($response);
            self::fail('Expected ApiException');
        } catch (ApiException $e) {
            self::assertSame(422, $e->getStatusCode());
            self::assertSame('API request failed', $e->getMessage());
        }
    }

    public function test_mapResponse_preserves_trace_id_for_non_json_error_bodies(): void
    {
        $response = new Response(503, ['X-Trace-Id' => 'trace-9'], '<html>503</html>');

        try {
            $this->mapper->mapResponse($response);
            self::fail('Expected ApiException');
        } catch (ApiException $e) {
            self::assertSame('trace-9', $e->getTraceId());
        }
    }

    public function test_mapResponse_wraps_malformed_json_on_a_successful_response(): void
    {
        $response = new Response(200, ['X-Trace-Id' => 'trace-1'], '{"id":');

        try {
            $this->mapper->mapResponse($response);
            self::fail('Expected ApiException');
        } catch (ApiException $e) {
            self::assertSame('API returned a malformed JSON response', $e->getMessage());
            self::assertSame(200, $e->getStatusCode());
            self::assertSame('trace-1', $e->getTraceId());
            self::assertInstanceOf(\JsonException::class, $e->getPrevious());
        }
    }

    public function test_readBody_returns_the_raw_body_on_an_expected_status(): void
    {
        $response = new Response(200, [], "\x89PNG\r\n\x1a\n binary");

        self::assertSame("\x89PNG\r\n\x1a\n binary", $this->mapper->readBody($response));
    }

    public function test_readBody_throws_with_the_api_error_message(): void
    {
        $response = new Response(404, ['X-Trace-Id' => 'trace-2'], '{"error":"File not found"}');

        try {
            $this->mapper->readBody($response);
            self::fail('Expected ApiException');
        } catch (ApiException $e) {
            self::assertSame('File not found', $e->getMessage());
            self::assertSame(404, $e->getStatusCode());
            self::assertSame('trace-2', $e->getTraceId());
        }
    }

    public function test_readBody_accepts_custom_expected_status_codes(): void
    {
        $response = new Response(204, [], '');

        self::assertSame('', $this->mapper->readBody($response, [204]));
    }
}
