<?php

declare(strict_types=1);

namespace Dropshipping\Tests\Unit\Support;

use Dropshipping\Exceptions\DropshippingException;
use Dropshipping\Support\Validator;
use PHPUnit\Framework\TestCase;

final class ValidatorTest extends TestCase
{
    public function test_requireNonEmpty_passes_for_non_empty_string(): void
    {
        Validator::requireNonEmpty('hello', 'field');
        $this->addToAssertionCount(1);
    }

    public function test_requireNonEmpty_throws_for_empty_string(): void
    {
        $this->expectException(DropshippingException::class);
        $this->expectExceptionMessage('"field" must not be empty');

        Validator::requireNonEmpty('', 'field');
    }

    public function test_requireStringLength_passes_within_range(): void
    {
        Validator::requireStringLength('abc', 'field', 1, 5);
        $this->addToAssertionCount(1);
    }

    public function test_requireStringLength_throws_below_min(): void
    {
        $this->expectException(DropshippingException::class);

        Validator::requireStringLength('', 'field', 1, 5);
    }

    public function test_requireStringLength_throws_above_max(): void
    {
        $this->expectException(DropshippingException::class);

        Validator::requireStringLength('toolong', 'field', 1, 3);
    }

    public function test_requireIntRange_passes_within_range(): void
    {
        Validator::requireIntRange(5, 'field', 1, 10);
        $this->addToAssertionCount(1);
    }

    public function test_requireIntRange_throws_below_min(): void
    {
        $this->expectException(DropshippingException::class);

        Validator::requireIntRange(0, 'field', 1, 10);
    }

    public function test_requireIntRange_throws_above_max(): void
    {
        $this->expectException(DropshippingException::class);

        Validator::requireIntRange(11, 'field', 1, 10);
    }

    public function test_requireEmail_passes_for_valid_email(): void
    {
        Validator::requireEmail('test@example.com', 'email');
        $this->addToAssertionCount(1);
    }

    public function test_requireEmail_throws_for_invalid_email(): void
    {
        $this->expectException(DropshippingException::class);
        $this->expectExceptionMessage('"email" must be a valid email');

        Validator::requireEmail('not-an-email', 'email');
    }

    public function test_requireNonEmptyArray_passes_for_non_empty(): void
    {
        Validator::requireNonEmptyArray(['item'], 'items');
        $this->addToAssertionCount(1);
    }

    public function test_requireNonEmptyArray_throws_for_empty(): void
    {
        $this->expectException(DropshippingException::class);
        $this->expectExceptionMessage('"items" must contain at least one item');

        Validator::requireNonEmptyArray([], 'items');
    }
}
