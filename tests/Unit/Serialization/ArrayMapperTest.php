<?php

declare(strict_types=1);

namespace Dropshipping\Tests\Unit\Serialization;

use Dropshipping\Exceptions\DropshippingException;
use Dropshipping\Serialization\ArrayMapper;
use PHPUnit\Framework\TestCase;

final class ArrayMapperTest extends TestCase
{
    private ArrayMapper $mapper;

    protected function setUp(): void
    {
        $this->mapper = new ArrayMapper();
    }

    public function test_decode_valid_json(): void
    {
        $result = $this->mapper->decode('{"key":"value","num":42}');

        self::assertSame(['key' => 'value', 'num' => 42], $result);
    }

    public function test_decode_throws_on_invalid_json(): void
    {
        $this->expectException(\JsonException::class);

        $this->mapper->decode('not-json');
    }

    public function test_decode_throws_on_non_array_json(): void
    {
        $this->expectException(DropshippingException::class);
        $this->expectExceptionMessage('JSON payload must decode to an array');

        $this->mapper->decode('"just a string"');
    }

    public function test_encode_produces_valid_json(): void
    {
        $result = $this->mapper->encode(['key' => 'value']);

        self::assertSame('{"key":"value"}', $result);
    }

    public function test_encode_preserves_unicode(): void
    {
        $result = $this->mapper->encode(['city' => 'München']);

        self::assertStringContainsString('München', $result);
        self::assertStringNotContainsString('\u', $result);
    }
}
