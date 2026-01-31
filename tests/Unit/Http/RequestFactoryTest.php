<?php

declare(strict_types=1);

namespace Dropshipping\Tests\Unit\Http;

use Dropshipping\Client\Authentication\ApiKeyAuthenticator;
use Dropshipping\Http\RequestFactory;
use Dropshipping\Serialization\ArrayMapper;
use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\TestCase;

final class RequestFactoryTest extends TestCase
{
    private RequestFactory $factory;

    protected function setUp(): void
    {
        $psr17 = new Psr17Factory();
        $this->factory = new RequestFactory(
            $psr17,
            $psr17,
            new ApiKeyAuthenticator('user', 'pass'),
            new ArrayMapper(),
        );
    }

    public function test_createJsonRequest_sets_method_and_url(): void
    {
        $request = $this->factory->createJsonRequest('POST', 'https://api.example.com/orders');

        self::assertSame('POST', $request->getMethod());
        self::assertSame('https://api.example.com/orders', (string) $request->getUri());
    }

    public function test_createJsonRequest_sets_content_type_and_accept(): void
    {
        $request = $this->factory->createJsonRequest('POST', 'https://example.com');

        self::assertSame('application/json', $request->getHeaderLine('Content-Type'));
        self::assertSame('application/json', $request->getHeaderLine('Accept'));
    }

    public function test_createJsonRequest_sets_auth_header(): void
    {
        $request = $this->factory->createJsonRequest('GET', 'https://example.com');

        $expected = 'Basic ' . base64_encode('user:pass');
        self::assertSame($expected, $request->getHeaderLine('Authorization'));
    }

    public function test_createJsonRequest_encodes_body_as_json(): void
    {
        $body = ['externalId' => 'test-123', 'email' => 'test@example.com'];
        $request = $this->factory->createJsonRequest('POST', 'https://example.com', $body);

        $content = (string) $request->getBody();
        $decoded = json_decode($content, true);

        self::assertSame($body, $decoded);
    }

    public function test_createJsonRequest_empty_body_has_no_content(): void
    {
        $request = $this->factory->createJsonRequest('GET', 'https://example.com');

        self::assertSame('', (string) $request->getBody());
    }

    public function test_createMultipartRequest_sets_boundary_in_content_type(): void
    {
        $request = $this->factory->createMultipartRequest(
            'POST',
            'https://example.com',
            '{}',
            'test-boundary-123',
        );

        self::assertSame(
            'multipart/form-data; boundary=test-boundary-123',
            $request->getHeaderLine('Content-Type'),
        );
    }

    public function test_buildMultipartBody_includes_order_json_part(): void
    {
        $orderJson = '{"externalId":"test"}';
        $body = $this->factory->buildMultipartBody($orderJson, [], 'boundary');

        self::assertStringContainsString('Content-Disposition: form-data; name="order"', $body);
        self::assertStringContainsString($orderJson, $body);
    }

    public function test_buildMultipartBody_includes_file_parts(): void
    {
        $tmpFile = tempnam(sys_get_temp_dir(), 'test') . '.pdf';
        file_put_contents($tmpFile, 'fake-pdf-content');

        try {
            $body = $this->factory->buildMultipartBody('{}', [$tmpFile], 'boundary');

            self::assertStringContainsString('filename="' . basename($tmpFile) . '"', $body);
            self::assertStringContainsString('Content-Type: application/pdf', $body);
            self::assertStringContainsString('fake-pdf-content', $body);
        } finally {
            unlink($tmpFile);
        }
    }

    public function test_buildMultipartBody_detects_mime_types(): void
    {
        $files = [];
        $extensions = ['jpg' => 'image/jpeg', 'png' => 'image/png', 'pdf' => 'application/pdf', 'bin' => 'application/octet-stream'];

        foreach ($extensions as $ext => $expectedMime) {
            $file = tempnam(sys_get_temp_dir(), 'test') . '.' . $ext;
            file_put_contents($file, 'content');
            $files[$file] = $expectedMime;
        }

        try {
            foreach ($files as $file => $expectedMime) {
                $body = $this->factory->buildMultipartBody('{}', [$file], 'boundary');
                self::assertStringContainsString("Content-Type: $expectedMime", $body);
            }
        } finally {
            foreach (array_keys($files) as $file) {
                unlink($file);
            }
        }
    }
}
