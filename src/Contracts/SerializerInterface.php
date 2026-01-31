<?php

declare(strict_types=1);

namespace Dropshipping\Contracts;

interface SerializerInterface
{
    /** @return array<string, mixed> */
    public function decode(string $json): array;

    /** @param array<string, mixed> $data */
    public function encode(array $data): string;
}
