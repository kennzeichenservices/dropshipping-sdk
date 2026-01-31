<?php

declare(strict_types=1);

namespace Dropshipping\Support;

use Dropshipping\Exceptions\DropshippingException;

final class Validator
{
    public static function requireNonEmpty(string $value, string $field): void
    {
        if ($value === '') {
            throw new DropshippingException(sprintf('Field "%s" must not be empty', $field));
        }
    }

    public static function requireStringLength(string $value, string $field, int $min, int $max): void
    {
        $length = mb_strlen($value);

        if ($length < $min || $length > $max) {
            throw new DropshippingException(
                sprintf('Field "%s" must be between %d and %d characters, got %d', $field, $min, $max, $length),
            );
        }
    }

    public static function requireIntRange(int $value, string $field, int $min, int $max): void
    {
        if ($value < $min || $value > $max) {
            throw new DropshippingException(
                sprintf('Field "%s" must be between %d and %d, got %d', $field, $min, $max, $value),
            );
        }
    }

    public static function requireEmail(string $value, string $field): void
    {
        if (filter_var($value, FILTER_VALIDATE_EMAIL) === false) {
            throw new DropshippingException(sprintf('Field "%s" must be a valid email address', $field));
        }
    }

    /** @param list<mixed> $items */
    public static function requireNonEmptyArray(array $items, string $field): void
    {
        if ($items === []) {
            throw new DropshippingException(sprintf('Field "%s" must contain at least one item', $field));
        }
    }
}
