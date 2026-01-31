<?php

declare(strict_types=1);

namespace Dropshipping\Serialization;

use Dropshipping\Contracts\SerializerInterface;
use Dropshipping\Exceptions\DropshippingException;

final class ArrayMapper implements SerializerInterface
{
    /** @return array<string, mixed> */
    public function decode(string $json): array
    {
        $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);

        if (!is_array($data)) {
            throw new DropshippingException('JSON payload must decode to an array');
        }

        return $data;
    }

    /** @param array<string, mixed> $data */
    public function encode(array $data): string
    {
        return json_encode($data, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
    }
}
