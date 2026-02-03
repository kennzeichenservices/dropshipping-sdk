<?php

declare(strict_types=1);

namespace Dropshipping\Serialization;

use Dropshipping\Contracts\SerializerInterface;
use Dropshipping\Exceptions\DropshippingException;

/**
 * Default serializer implementation using json_encode/json_decode.
 */
final class ArrayMapper implements SerializerInterface
{
    /**
     * Parse a JSON string into an associative array.
     *
     * @param  string $json JSON-encoded string
     * @return array<string, mixed>
     *
     * @throws DropshippingException If the decoded value is not an array.
     */
    public function decode(string $json): array
    {
        $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);

        if (!is_array($data)) {
            throw new DropshippingException('JSON payload must decode to an array');
        }

        return $data;
    }

    /**
     * Convert an associative array to a JSON string.
     *
     * @param array<string, mixed> $data Data to encode.
     *
     * @return string JSON-encoded string.
     */
    public function encode(array $data): string
    {
        return json_encode($data, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
    }
}
