<?php

declare(strict_types=1);

namespace Dropshipping\Support;

use BackedEnum;
use Dropshipping\Exceptions\DropshippingException;

/**
 * Reads values out of decoded API payloads with descriptive errors.
 *
 * Without this, a missing or retyped field surfaces as a raw \TypeError and an
 * unmodelled enum value as a raw \ValueError. Neither is a
 * {@see DropshippingException}, so callers guarding an SDK call cannot catch them —
 * a partial response or a newly added API enum value takes down the host
 * application instead of being reported as an API problem.
 *
 * Every accessor names the field and the DTO it belongs to, because the same key
 * (`seasonStartMonth`, say) appears in half a dozen payloads.
 */
final class Hydrator
{
    /**
     * @param array<string, mixed> $data
     *
     * @throws DropshippingException If the field is absent or not a string.
     */
    public static function requireString(array $data, string $key, string $context): string
    {
        $value = self::read($data, $key, $context);

        if (!is_string($value)) {
            throw self::typeMismatch($key, $context, 'string', $value);
        }

        return $value;
    }

    /**
     * @param array<string, mixed> $data
     *
     * @throws DropshippingException If the field is present but not a string.
     */
    public static function optionalString(array $data, string $key, string $context): ?string
    {
        $value = $data[$key] ?? null;

        if ($value === null) {
            return null;
        }

        if (!is_string($value)) {
            throw self::typeMismatch($key, $context, 'string', $value);
        }

        return $value;
    }

    /**
     * @param array<string, mixed> $data
     *
     * @throws DropshippingException If the field is absent or not an integer.
     */
    public static function requireInt(array $data, string $key, string $context): int
    {
        $value = self::read($data, $key, $context);

        if (!is_int($value)) {
            throw self::typeMismatch($key, $context, 'int', $value);
        }

        return $value;
    }

    /**
     * @param array<string, mixed> $data
     *
     * @throws DropshippingException If the field is present but not an integer.
     */
    public static function optionalInt(array $data, string $key, string $context): ?int
    {
        $value = $data[$key] ?? null;

        if ($value === null) {
            return null;
        }

        if (!is_int($value)) {
            throw self::typeMismatch($key, $context, 'int', $value);
        }

        return $value;
    }

    /**
     * @param array<string, mixed> $data
     *
     * @throws DropshippingException If the field is absent or not a boolean.
     */
    public static function requireBool(array $data, string $key, string $context): bool
    {
        $value = self::read($data, $key, $context);

        if (!is_bool($value)) {
            throw self::typeMismatch($key, $context, 'bool', $value);
        }

        return $value;
    }

    /**
     * @param array<string, mixed> $data
     *
     * @throws DropshippingException If the field is present but not a boolean.
     */
    public static function optionalBool(array $data, string $key, string $context): ?bool
    {
        $value = $data[$key] ?? null;

        if ($value === null) {
            return null;
        }

        if (!is_bool($value)) {
            throw self::typeMismatch($key, $context, 'bool', $value);
        }

        return $value;
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     *
     * @throws DropshippingException If the field is absent or not an array.
     */
    public static function requireArray(array $data, string $key, string $context): array
    {
        $value = self::read($data, $key, $context);

        if (!is_array($value)) {
            throw self::typeMismatch($key, $context, 'array', $value);
        }

        /** @var array<string, mixed> $value */
        return $value;
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>|null
     *
     * @throws DropshippingException If the field is present but not an array.
     */
    public static function optionalArray(array $data, string $key, string $context): ?array
    {
        $value = $data[$key] ?? null;

        if ($value === null) {
            return null;
        }

        if (!is_array($value)) {
            throw self::typeMismatch($key, $context, 'array', $value);
        }

        /** @var array<string, mixed> $value */
        return $value;
    }

    /**
     * Read a list of nested payloads, e.g. the `items` of a delivery.
     *
     * Returns a list even when the API sends a JSON object with non-sequential keys,
     * so callers can rely on the `list<...>` contract their constructors declare.
     *
     * @param array<string, mixed> $data
     * @return list<array<string, mixed>>
     *
     * @throws DropshippingException If the field is not an array of arrays.
     */
    public static function requireArrayList(array $data, string $key, string $context): array
    {
        return self::toArrayList(self::requireArray($data, $key, $context), $key, $context);
    }

    /**
     * Same as {@see requireArrayList()} but yields an empty list when the field is absent.
     *
     * @param array<string, mixed> $data
     * @return list<array<string, mixed>>
     *
     * @throws DropshippingException If the field is present but not an array of arrays.
     */
    public static function optionalArrayList(array $data, string $key, string $context): array
    {
        $value = self::optionalArray($data, $key, $context);

        return $value === null ? [] : self::toArrayList($value, $key, $context);
    }

    /**
     * Resolve a backed enum case, naming the offending value and the known cases.
     *
     * Pass $fallback for fields where the API may introduce values before this SDK
     * models them — forward compatibility beats an exception for those.
     *
     * @template T of BackedEnum
     *
     * @param class-string<T>      $enumClass
     * @param array<string, mixed> $data
     * @param T|null               $fallback
     *
     * @return T
     *
     * @throws DropshippingException If the value is unknown and no fallback was given.
     */
    public static function requireEnum(
        string $enumClass,
        array $data,
        string $key,
        string $context,
        ?BackedEnum $fallback = null,
    ): BackedEnum {
        $value = $fallback === null
            ? self::read($data, $key, $context)
            : $data[$key] ?? null;

        if (is_string($value) || is_int($value)) {
            try {
                $case = $enumClass::tryFrom($value);
            } catch (\TypeError) {
                // tryFrom() rejects a value of the wrong backing type outright — an int for
                // a string-backed enum, say. That is just another unsupported value here.
                $case = null;
            }

            if ($case !== null) {
                return $case;
            }
        }

        if ($fallback !== null) {
            return $fallback;
        }

        throw new DropshippingException(sprintf(
            'Unsupported value %s for field "%s" in %s. Known values: %s.',
            self::describe($value),
            $key,
            $context,
            implode(', ', array_map(
                static fn (BackedEnum $case): string => (string) $case->value,
                $enumClass::cases(),
            )),
        ));
    }

    /**
     * @param array<string, mixed> $data
     *
     * @throws DropshippingException If the key is absent or null.
     */
    private static function read(array $data, string $key, string $context): mixed
    {
        if (!array_key_exists($key, $data) || $data[$key] === null) {
            throw new DropshippingException(sprintf(
                'Missing required field "%s" in %s.',
                $key,
                $context,
            ));
        }

        return $data[$key];
    }

    /**
     * @param array<array-key, mixed> $value
     * @return list<array<string, mixed>>
     *
     * @throws DropshippingException If any element is not an array.
     */
    private static function toArrayList(array $value, string $key, string $context): array
    {
        $items = [];

        foreach ($value as $index => $item) {
            if (!is_array($item)) {
                throw self::typeMismatch(
                    sprintf('%s[%s]', $key, (string) $index),
                    $context,
                    'array',
                    $item,
                );
            }

            /** @var array<string, mixed> $item */
            $items[] = $item;
        }

        return $items;
    }

    private static function typeMismatch(string $key, string $context, string $expected, mixed $actual): DropshippingException
    {
        return new DropshippingException(sprintf(
            'Field "%s" in %s must be of type %s, got %s.',
            $key,
            $context,
            $expected,
            self::describe($actual),
        ));
    }

    /**
     * Describe a value for an error message without dumping payload contents.
     */
    private static function describe(mixed $value): string
    {
        if (is_string($value)) {
            return sprintf('string("%s")', mb_substr($value, 0, 40));
        }

        return get_debug_type($value);
    }
}
