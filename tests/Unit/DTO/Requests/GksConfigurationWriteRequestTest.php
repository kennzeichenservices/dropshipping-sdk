<?php

declare(strict_types=1);

namespace Dropshipping\Tests\Unit\DTO\Requests;

use Dropshipping\DTO\Requests\GksConfigurationCompany;
use Dropshipping\DTO\Requests\GksConfigurationWriteRequest;
use Dropshipping\Exceptions\DropshippingException;
use PHPUnit\Framework\TestCase;

final class GksConfigurationWriteRequestTest extends TestCase
{
    public function test_toArray_includes_all_fields(): void
    {
        $company = new GksConfigurationCompany(
            name: 'Acme GmbH',
            streetName: 'Musterstraße',
            houseNumber: '42',
            zipCode: '10115',
            cityName: 'Berlin',
            countryCode: 'DE',
        );

        $request = new GksConfigurationWriteRequest(
            name: 'Test Config',
            kopaKey: 'kopa-123',
            username: 'gks-user',
            password: 'gks-pass',
            publicKeyCertificate: '-----BEGIN CERTIFICATE-----',
            privateKey: '-----BEGIN PRIVATE KEY-----',
            company: $company,
        );

        $array = $request->toArray();

        self::assertSame('Test Config', $array['name']);
        self::assertSame('kopa-123', $array['kopaKey']);
        self::assertSame('gks-user', $array['username']);
        self::assertSame('gks-pass', $array['password']);
        self::assertSame('-----BEGIN CERTIFICATE-----', $array['publicKeyCertificate']);
        self::assertSame('-----BEGIN PRIVATE KEY-----', $array['privateKey']);
        self::assertSame('Acme GmbH', $array['company']['name']);
        self::assertSame('Musterstraße', $array['company']['streetName']);
        self::assertSame('42', $array['company']['houseNumber']);
        self::assertSame('10115', $array['company']['zipCode']);
        self::assertSame('Berlin', $array['company']['cityName']);
        self::assertSame('DE', $array['company']['countryCode']);
    }

    public function test_validates_name_length(): void
    {
        $this->expectException(DropshippingException::class);

        $company = new GksConfigurationCompany('Co', 'St', '1', '1', 'City', 'DE');

        new GksConfigurationWriteRequest(
            name: '',
            kopaKey: 'key',
            username: 'user',
            password: 'pass',
            publicKeyCertificate: 'cert',
            privateKey: 'key',
            company: $company,
        );
    }

    public function test_validates_empty_kopaKey(): void
    {
        $this->expectException(DropshippingException::class);

        $company = new GksConfigurationCompany('Co', 'St', '1', '1', 'City', 'DE');

        new GksConfigurationWriteRequest(
            name: 'Config',
            kopaKey: '',
            username: 'user',
            password: 'pass',
            publicKeyCertificate: 'cert',
            privateKey: 'key',
            company: $company,
        );
    }

    public function test_company_validates_empty_name(): void
    {
        $this->expectException(DropshippingException::class);

        new GksConfigurationCompany(
            name: '',
            streetName: 'St',
            houseNumber: '1',
            zipCode: '1',
            cityName: 'City',
            countryCode: 'DE',
        );
    }
}
