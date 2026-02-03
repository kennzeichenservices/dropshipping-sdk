<?php

declare(strict_types=1);

namespace Dropshipping\Support;

use Dropshipping\Exceptions\DropshippingException;

/**
 * Utility class providing static validation methods for common field constraints.
 */
final class Validator
{
    /**
     * Ensure that a string value is not empty.
     *
     * @param string $value The value to validate.
     * @param string $field The field name used in the exception message.
     *
     * @throws DropshippingException If the value is an empty string.
     */
    public static function requireNonEmpty(string $value, string $field): void
    {
        if ($value === '') {
            throw new DropshippingException(sprintf('Field "%s" must not be empty', $field));
        }
    }

    /**
     * Validate that a string length falls within the given range.
     *
     * @param string $value The value to validate.
     * @param string $field The field name used in the exception message.
     * @param int    $min   Minimum allowed length (inclusive).
     * @param int    $max   Maximum allowed length (inclusive).
     *
     * @throws DropshippingException If the string length is outside the allowed range.
     */
    public static function requireStringLength(string $value, string $field, int $min, int $max): void
    {
        $length = mb_strlen($value);

        if ($length < $min || $length > $max) {
            throw new DropshippingException(
                sprintf('Field "%s" must be between %d and %d characters, got %d', $field, $min, $max, $length),
            );
        }
    }

    /**
     * Validate that an integer value falls within the given range.
     *
     * @param int    $value The value to validate.
     * @param string $field The field name used in the exception message.
     * @param int    $min   Minimum allowed value (inclusive).
     * @param int    $max   Maximum allowed value (inclusive).
     *
     * @throws DropshippingException If the value is outside the allowed range.
     */
    public static function requireIntRange(int $value, string $field, int $min, int $max): void
    {
        if ($value < $min || $value > $max) {
            throw new DropshippingException(
                sprintf('Field "%s" must be between %d and %d, got %d', $field, $min, $max, $value),
            );
        }
    }

    /**
     * Validate that a value is a well-formed email address.
     *
     * @param string $value The value to validate.
     * @param string $field The field name used in the exception message.
     *
     * @throws DropshippingException If the value is not a valid email address.
     */
    public static function requireEmail(string $value, string $field): void
    {
        if (filter_var($value, FILTER_VALIDATE_EMAIL) === false) {
            throw new DropshippingException(sprintf('Field "%s" must be a valid email address', $field));
        }
    }

    /**
     * Ensure that an array contains at least one item.
     *
     * @param list<mixed> $items The array to validate.
     * @param string      $field The field name used in the exception message.
     *
     * @throws DropshippingException If the array is empty.
     */
    public static function requireNonEmptyArray(array $items, string $field): void
    {
        if ($items === []) {
            throw new DropshippingException(sprintf('Field "%s" must contain at least one item', $field));
        }
    }
}
