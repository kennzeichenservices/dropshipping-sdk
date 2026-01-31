<?php

declare(strict_types=1);

namespace Dropshipping\Tests\Unit\DTO\Requests;

use Dropshipping\DTO\Address;
use Dropshipping\DTO\EuroLicensePlateNumberComponents;
use Dropshipping\DTO\Requests\LicensePlateReservationCustomization;
use Dropshipping\DTO\Requests\LicensePlateReservationRequest;
use Dropshipping\DTO\Requests\LicensePlateReservationVehicleHolder;
use Dropshipping\Enums\Gender;
use Dropshipping\Enums\LicensePlateType;
use Dropshipping\Enums\VehicleType;
use PHPUnit\Framework\TestCase;

final class LicensePlateReservationRequestTest extends TestCase
{
    public function test_toArray_includes_all_fields(): void
    {
        $address = new Address('Max', 'Mustermann', Gender::Male, 'Str', '1', '12345', 'Berlin', 'DE');
        $components = new EuroLicensePlateNumberComponents('B', 'AB', '123');
        $customization = new LicensePlateReservationCustomization(
            registrationOfficeServiceId: 100,
            licensePlateType: LicensePlateType::Regular,
            vehicleType: VehicleType::Car,
            licensePlateNumberComponents: $components,
        );
        $vehicleHolder = new LicensePlateReservationVehicleHolder(
            address: $address,
            birthDate: '1990-01-01',
            placeOfBirth: 'Berlin',
        );

        $request = new LicensePlateReservationRequest(
            email: 'test@example.com',
            customization: $customization,
            vehicleHolder: $vehicleHolder,
            externalOrderId: 'ext-1',
        );

        $array = $request->toArray();

        self::assertSame('test@example.com', $array['email']);
        self::assertSame('ext-1', $array['externalOrderId']);
        self::assertArrayHasKey('customization', $array);
        self::assertArrayHasKey('vehicleHolder', $array);
        self::assertSame(100, $array['customization']['registrationOfficeServiceId']);
        self::assertSame('1990-01-01', $array['vehicleHolder']['birthDate']);
    }

    public function test_toArray_excludes_null_externalOrderId(): void
    {
        $address = new Address('Max', 'Mustermann', Gender::Male, 'Str', '1', '12345', 'Berlin', 'DE');
        $components = new EuroLicensePlateNumberComponents('B', 'AB', '123');
        $customization = new LicensePlateReservationCustomization(1, LicensePlateType::Regular, VehicleType::Car, $components);
        $vehicleHolder = new LicensePlateReservationVehicleHolder($address);

        $request = new LicensePlateReservationRequest('test@example.com', $customization, $vehicleHolder);
        $array = $request->toArray();

        self::assertArrayNotHasKey('externalOrderId', $array);
    }

    public function test_customization_fromArray(): void
    {
        $data = [
            'registrationOfficeServiceId' => 50,
            'licensePlateType' => 'ELECTRIC',
            'vehicleType' => 'CAR',
            'licensePlateNumberComponents' => ['city' => 'M', 'middle' => 'XY', 'end' => '1'],
        ];

        $customization = LicensePlateReservationCustomization::fromArray($data);

        self::assertSame(50, $customization->registrationOfficeServiceId);
        self::assertSame(LicensePlateType::Electric, $customization->licensePlateType);
        self::assertSame('M', $customization->licensePlateNumberComponents->city);
    }
}
