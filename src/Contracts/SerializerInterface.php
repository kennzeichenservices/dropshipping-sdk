<?php

declare(strict_types=1);

namespace Dropshipping\Contracts;

/**
 * Contract for JSON serialization and deserialization.
 */
interface SerializerInterface
{
    /**
     * Decode a JSON string into an associative array.
     *
     * @return array<string, mixed>
     */
    public function decode(string $json): array;

    /**
     * Encode an associative array into a JSON string.
     *
     * @param array<string, mixed> $data
     */
    public function encode(array $data): string;
}
