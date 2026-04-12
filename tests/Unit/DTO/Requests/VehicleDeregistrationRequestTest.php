<?php

declare(strict_types=1);

namespace Dropshipping\Tests\Unit\DTO\Requests;

use Dropshipping\DTO\Address;
use Dropshipping\DTO\EuroLicensePlateNumberComponents;
use Dropshipping\DTO\Requests\VehicleDeregistrationRequest;
use Dropshipping\DTO\Requests\VehicleDeregistrationVehicleHolder;
use Dropshipping\DTO\VehicleDeregistrationCustomization;
use Dropshipping\Enums\Gender;
use Dropshipping\Enums\VehicleDeregistrationLicensePlateType;
use Dropshipping\Enums\VehicleDeregistrationVehicleType;
use Dropshipping\Exceptions\DropshippingException;
use PHPUnit\Framework\TestCase;

final class VehicleDeregistrationRequestTest extends TestCase
{
    public function test_toArray_includes_all_fields(): void
    {
        $request = $this->createRequest(externalOrderId: 'ext-1', gksConfigurationId: 'uuid-123');
        $array = $request->toArray();

        self::assertSame('test@example.com', $array['email']);
        self::assertSame('ext-1', $array['externalOrderId']);
        self::assertSame('uuid-123', $array['gksConfigurationId']);
        self::assertArrayHasKey('customization', $array);
        self::assertArrayHasKey('vehicleHolder', $array);
        self::assertSame('VEHICLE_DEREGISTRATION', $array['customization']['productType']);
        self::assertSame('CAR', $array['customization']['vehicleType']);
        self::assertSame('REGULAR', $array['customization']['licensePlateType']);
        self::assertSame('WBA12345678901234', $array['customization']['vehicleIdentificationNumber']);
    }

    public function test_toArray_excludes_null_optional_fields(): void
    {
        $request = $this->createRequest();
        $array = $request->toArray();

        self::assertArrayNotHasKey('externalOrderId', $array);
        self::assertArrayNotHasKey('gksConfigurationId', $array);
    }

    public function test_customization_excludes_null_season_months(): void
    {
        $request = $this->createRequest();
        $array = $request->toArray();

        self::assertArrayNotHasKey('seasonStartMonth', $array['customization']);
        self::assertArrayNotHasKey('seasonEndMonth', $array['customization']);
        self::assertArrayNotHasKey('frontLicensePlateSecurityCode', $array['customization']);
    }

    public function test_customization_includes_optional_fields_when_set(): void
    {
        $components = new EuroLicensePlateNumberComponents('B', 'AB', '123');
        $customization = new VehicleDeregistrationCustomization(
            vehicleType: VehicleDeregistrationVehicleType::Car,
            licensePlateType: VehicleDeregistrationLicensePlateType::RegularSeason,
            licensePlateNumberComponents: $components,
            licensePlateReservationIncluded: true,
            vehicleIdentificationNumber: 'WBA12345678901234',
            vehicleRegistrationCertificateSecurityCode: 'SEC123',
            vehicleRegistrationDate: '2020-01-15',
            rearLicensePlateSecurityCode: 'REAR1',
            frontLicensePlateSecurityCode: 'FRONT1',
            seasonStartMonth: 3,
            seasonEndMonth: 10,
        );

        $array = $customization->toArray();

        self::assertSame('FRONT1', $array['frontLicensePlateSecurityCode']);
        self::assertSame(3, $array['seasonStartMonth']);
        self::assertSame(10, $array['seasonEndMonth']);
    }

    public function test_validates_email(): void
    {
        $this->expectException(DropshippingException::class);
        $this->createRequest(email: 'invalid');
    }

    public function test_validates_season_month_range(): void
    {
        $this->expectException(DropshippingException::class);

        $components = new EuroLicensePlateNumberComponents('B', 'AB', '123');

        new VehicleDeregistrationCustomization(
            vehicleType: VehicleDeregistrationVehicleType::Car,
            licensePlateType: VehicleDeregistrationLicensePlateType::Regular,
            licensePlateNumberComponents: $components,
            licensePlateReservationIncluded: false,
            vehicleIdentificationNumber: 'WBA12345678901234',
            vehicleRegistrationCertificateSecurityCode: 'SEC123',
            vehicleRegistrationDate: '2020-01-15',
            rearLicensePlateSecurityCode: 'REAR1',
            seasonStartMonth: 13,
        );
    }

    private function createRequest(
        string $email = 'test@example.com',
        ?string $externalOrderId = null,
        ?string $gksConfigurationId = null,
    ): VehicleDeregistrationRequest {
        $components = new EuroLicensePlateNumberComponents('B', 'AB', '123');
        $customization = new VehicleDeregistrationCustomization(
            vehicleType: VehicleDeregistrationVehicleType::Car,
            licensePlateType: VehicleDeregistrationLicensePlateType::Regular,
            licensePlateNumberComponents: $components,
            licensePlateReservationIncluded: false,
            vehicleIdentificationNumber: 'WBA12345678901234',
            vehicleRegistrationCertificateSecurityCode: 'SEC123',
            vehicleRegistrationDate: '2020-01-15',
            rearLicensePlateSecurityCode: 'REAR1',
        );
        $address = new Address('Max', 'Mustermann', Gender::Male, 'Str', '1', '12345', 'Berlin', 'DE');
        $vehicleHolder = new VehicleDeregistrationVehicleHolder($address);

        return new VehicleDeregistrationRequest(
            email: $email,
            customization: $customization,
            vehicleHolder: $vehicleHolder,
            externalOrderId: $externalOrderId,
            gksConfigurationId: $gksConfigurationId,
        );
    }
}
