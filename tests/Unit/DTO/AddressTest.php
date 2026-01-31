<?php

declare(strict_types=1);

namespace Dropshipping\Tests\Unit\DTO;

use Dropshipping\DTO\Address;
use Dropshipping\Enums\Gender;
use PHPUnit\Framework\TestCase;

final class AddressTest extends TestCase
{
    public function test_toArray_includes_required_fields(): void
    {
        $address = new Address(
            firstName: 'Max',
            lastName: 'Mustermann',
            gender: Gender::Male,
            streetName: 'Musterstraße',
            houseNumber: '1',
            zipCode: '12345',
            cityName: 'Berlin',
            countryCode: 'DE',
        );

        $array = $address->toArray();

        self::assertSame('Max', $array['firstName']);
        self::assertSame('Mustermann', $array['lastName']);
        self::assertSame('MALE', $array['gender']);
        self::assertSame('Musterstraße', $array['streetName']);
        self::assertSame('1', $array['houseNumber']);
        self::assertSame('12345', $array['zipCode']);
        self::assertSame('Berlin', $array['cityName']);
        self::assertSame('DE', $array['countryCode']);
    }

    public function test_toArray_excludes_null_optional_fields(): void
    {
        $address = new Address('Max', 'Mustermann', Gender::Male, 'Str', '1', '12345', 'Berlin', 'DE');
        $array = $address->toArray();

        self::assertArrayNotHasKey('taxNumber', $array);
        self::assertArrayNotHasKey('companyName', $array);
        self::assertArrayNotHasKey('additionalField', $array);
        self::assertArrayNotHasKey('phoneNumber', $array);
    }

    public function test_toArray_includes_optional_fields_when_set(): void
    {
        $address = new Address(
            'Max', 'Mustermann', Gender::Male, 'Str', '1', '12345', 'Berlin', 'DE',
            taxNumber: 'DE123456789',
            companyName: 'Test GmbH',
            additionalField: 'Hinterhaus',
            phoneNumber: '+49123456',
        );
        $array = $address->toArray();

        self::assertSame('DE123456789', $array['taxNumber']);
        self::assertSame('Test GmbH', $array['companyName']);
        self::assertSame('Hinterhaus', $array['additionalField']);
        self::assertSame('+49123456', $array['phoneNumber']);
    }

    public function test_fromArray_creates_instance(): void
    {
        $data = [
            'firstName' => 'Erika',
            'lastName' => 'Musterfrau',
            'gender' => 'FEMALE',
            'streetName' => 'Hauptstr.',
            'houseNumber' => '10',
            'zipCode' => '54321',
            'cityName' => 'München',
            'countryCode' => 'DE',
            'companyName' => 'Firma AG',
        ];

        $address = Address::fromArray($data);

        self::assertSame('Erika', $address->firstName);
        self::assertSame(Gender::Female, $address->gender);
        self::assertSame('Firma AG', $address->companyName);
        self::assertNull($address->taxNumber);
    }
}
