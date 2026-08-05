<?php

declare(strict_types=1);

namespace Dropshipping\Tests\Unit\Support;

use Dropshipping\Enums\Gender;
use Dropshipping\Exceptions\DropshippingException;
use Dropshipping\Support\Hydrator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class HydratorTest extends TestCase
{
    private const CTX = 'TestPayload';

    public function test_requireString_returns_the_value(): void
    {
        self::assertSame('abc', Hydrator::requireString(['a' => 'abc'], 'a', self::CTX));
    }

    public function test_requireString_names_the_field_and_the_payload(): void
    {
        $this->expectException(DropshippingException::class);
        $this->expectExceptionMessage('Missing required field "middle" in TestPayload.');

        Hydrator::requireString([], 'middle', self::CTX);
    }

    public function test_requireString_rejects_a_wrong_type_without_coercing(): void
    {
        $this->expectException(DropshippingException::class);
        $this->expectExceptionMessage('Field "a" in TestPayload must be of type string, got int.');

        Hydrator::requireString(['a' => 42], 'a', self::CTX);
    }

    public function test_requireString_treats_an_explicit_null_as_missing(): void
    {
        $this->expectException(DropshippingException::class);
        $this->expectExceptionMessage('Missing required field "a"');

        Hydrator::requireString(['a' => null], 'a', self::CTX);
    }

    public function test_optionalString_returns_null_for_absent_and_null_values(): void
    {
        self::assertNull(Hydrator::optionalString([], 'a', self::CTX));
        self::assertNull(Hydrator::optionalString(['a' => null], 'a', self::CTX));
        self::assertSame('x', Hydrator::optionalString(['a' => 'x'], 'a', self::CTX));
    }

    public function test_requireInt_does_not_accept_numeric_strings(): void
    {
        $this->expectException(DropshippingException::class);
        $this->expectExceptionMessage('must be of type int, got string("7")');

        Hydrator::requireInt(['a' => '7'], 'a', self::CTX);
    }

    public function test_requireBool_rejects_truthy_values(): void
    {
        $this->expectException(DropshippingException::class);

        Hydrator::requireBool(['a' => 1], 'a', self::CTX);
    }

    public function test_optionalBool_preserves_false(): void
    {
        self::assertFalse(Hydrator::optionalBool(['a' => false], 'a', self::CTX));
    }

    /**
     * The API may return a JSON object with non-sequential keys where the DTOs declare
     * `list<...>`. Reindexing here keeps that contract from being violated silently.
     */
    public function test_requireArrayList_reindexes_non_sequential_keys(): void
    {
        $data = ['items' => [3 => ['id' => 1], 7 => ['id' => 2]]];

        self::assertSame(
            [['id' => 1], ['id' => 2]],
            Hydrator::requireArrayList($data, 'items', self::CTX),
        );
    }

    public function test_requireArrayList_reports_the_offending_index(): void
    {
        $this->expectException(DropshippingException::class);
        $this->expectExceptionMessage('Field "items[1]" in TestPayload must be of type array, got string');

        Hydrator::requireArrayList(['items' => [['ok' => true], 'nope']], 'items', self::CTX);
    }

    public function test_optionalArrayList_yields_an_empty_list_when_absent(): void
    {
        self::assertSame([], Hydrator::optionalArrayList([], 'items', self::CTX));
    }

    public function test_requireEnum_resolves_a_known_case(): void
    {
        self::assertSame(
            Gender::Male,
            Hydrator::requireEnum(Gender::class, ['gender' => 'MALE'], 'gender', self::CTX),
        );
    }

    /**
     * Previously `Gender::from()` raised a raw \ValueError, which is not a
     * DropshippingException and therefore escaped every caller's catch block.
     *
     * @param mixed $value A value the API might send that this SDK does not model.
     */
    #[DataProvider('unmodelledEnumValues')]
    public function test_requireEnum_reports_unknown_values_as_sdk_exceptions(mixed $value): void
    {
        try {
            Hydrator::requireEnum(Gender::class, ['gender' => $value], 'gender', self::CTX);
            self::fail('Expected DropshippingException');
        } catch (DropshippingException $e) {
            self::assertStringContainsString('gender', $e->getMessage());
            self::assertStringContainsString('Known values:', $e->getMessage());
        }
    }

    /**
     * @return array<string, array{mixed}>
     */
    public static function unmodelledEnumValues(): array
    {
        return [
            'new string value' => ['DIVERSE'],
            'wrong type: int' => [42],
            'wrong type: array' => [['MALE']],
            'wrong type: bool' => [true],
        ];
    }

    public function test_requireEnum_falls_back_instead_of_throwing_when_given_a_default(): void
    {
        self::assertSame(
            Gender::Female,
            Hydrator::requireEnum(Gender::class, ['gender' => 'NEW_VALUE'], 'gender', self::CTX, Gender::Female),
        );
    }

    public function test_requireEnum_falls_back_when_the_field_is_absent(): void
    {
        self::assertSame(
            Gender::Female,
            Hydrator::requireEnum(Gender::class, [], 'gender', self::CTX, Gender::Female),
        );
    }

    public function test_error_messages_truncate_long_values(): void
    {
        try {
            Hydrator::requireInt(['a' => str_repeat('x', 500)], 'a', self::CTX);
            self::fail('Expected DropshippingException');
        } catch (DropshippingException $e) {
            self::assertLessThan(120, mb_strlen($e->getMessage()));
        }
    }
}
