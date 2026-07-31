<?php

declare(strict_types=1);

namespace Dropshipping\Tests\Unit\DTO\Requests;

use Dropshipping\DTO\Address;
use Dropshipping\DTO\EuroLicensePlateNumberComponents;
use Dropshipping\DTO\Requests\VehicleRegistrationRequest;
use Dropshipping\DTO\Requests\VehicleRegistrationVehicleHolder;
use Dropshipping\DTO\VehicleRegistrationCustomization;
use Dropshipping\DTO\VehicleRegistrationLicensePlateNumberAssignmentStrategyRandom;
use Dropshipping\DTO\VehicleRegistrationLicensePlateNumberAssignmentStrategyReservation;
use Dropshipping\Enums\Gender;
use Dropshipping\Enums\VehicleRegistrationLicensePlateType;
use Dropshipping\Enums\VehicleRegistrationServiceTypeCode;
use Dropshipping\Enums\VehicleRegistrationVehicleType;
use Dropshipping\Exceptions\DropshippingException;
use PHPUnit\Framework\TestCase;

final class VehicleRegistrationRequestTest extends TestCase
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
        self::assertSame('VEHICLE_REGISTRATION', $array['customization']['productType']);
        self::assertSame('RANDOM', $array['customization']['licensePlateNumberAssignmentStrategy']['strategyType']);
        self::assertSame('NZ', $array['customization']['vehicleRegistrationServiceTypeCode']);
        self::assertSame('CAR', $array['customization']['vehicleType']);
        self::assertSame('WBA12345678901234', $array['customization']['vehicleIdentificationNumber']);
    }

    public function test_toArray_includes_contractPartnerKopaKey(): void
    {
        $request = $this->createRequest(contractPartnerKopaKey: 'K123X');
        $array = $request->toArray();

        self::assertSame('K123X', $array['contractPartnerKopaKey']);
        self::assertArrayNotHasKey('gksConfigurationId', $array);
    }

    public function test_toArray_excludes_null_optional_fields(): void
    {
        $array = $this->createRequest()->toArray();

        self::assertArrayNotHasKey('externalOrderId', $array);
        self::assertArrayNotHasKey('gksConfigurationId', $array);
        self::assertArrayNotHasKey('contractPartnerKopaKey', $array);
    }

    public function test_vehicleHolder_contains_address_and_birth_details(): void
    {
        $array = $this->createRequest()->toArray();

        self::assertSame(['address', 'placeOfBirth', 'birthDate'], array_keys($array['vehicleHolder']));
        self::assertSame('Mustermann', $array['vehicleHolder']['address']['lastName']);
        self::assertSame('Berlin', $array['vehicleHolder']['placeOfBirth']);
        self::assertSame('1990-01-31', $array['vehicleHolder']['birthDate']);
    }

    public function test_vehicleHolder_omits_birthName_when_null(): void
    {
        $array = $this->createVehicleHolder()->toArray();

        self::assertArrayNotHasKey('birthName', $array);
    }

    public function test_vehicleHolder_rejects_empty_placeOfBirth(): void
    {
        $this->expectException(DropshippingException::class);

        new VehicleRegistrationVehicleHolder(
            address: $this->createAddress(),
            placeOfBirth: '',
            birthDate: '1990-01-31',
        );
    }

    public function test_vehicleHolder_includes_birthName_when_set(): void
    {
        $holder = new VehicleRegistrationVehicleHolder(
            address: $this->createAddress(),
            placeOfBirth: 'Berlin',
            birthDate: '1990-01-31',
            birthName: 'Musterfrau',
        );

        self::assertSame('Musterfrau', $holder->toArray()['birthName']);
    }

    public function test_vehicleHolder_rejects_too_long_placeOfBirth(): void
    {
        $this->expectException(DropshippingException::class);

        new VehicleRegistrationVehicleHolder(
            address: $this->createAddress(),
            placeOfBirth: str_repeat('a', 151),
            birthDate: '1990-01-31',
        );
    }

    public function test_vehicleHolder_rejects_empty_birthDate(): void
    {
        $this->expectException(DropshippingException::class);

        new VehicleRegistrationVehicleHolder(
            address: $this->createAddress(),
            placeOfBirth: 'Berlin',
            birthDate: '',
        );
    }

    public function test_reserved_licensePlateNumberComponents_carry_euro_usage_type(): void
    {
        $customization = new VehicleRegistrationCustomization(
            licensePlateNumberAssignmentStrategy: new VehicleRegistrationLicensePlateNumberAssignmentStrategyReservation(
                licensePlateNumberComponents: new EuroLicensePlateNumberComponents('B', 'AB', '123'),
                licensePlateType: VehicleRegistrationLicensePlateType::Regular,
                reservationPin: '1234',
            ),
            vehicleRegistrationServiceTypeCode: VehicleRegistrationServiceTypeCode::NZ,
            deregistered: false,
            vehicleType: VehicleRegistrationVehicleType::Car,
            electronicInsuranceConfirmationNumber: 'ABC1234',
            vehicleIdentificationNumber: 'WBA12345678901234',
            vehicleTitleSecurityCode: 'ABCDEF123456',
            iban: 'DE89370400440532013000',
            bic: 'COBADEFFXXX',
        );

        $strategy = $customization->toArray()['licensePlateNumberAssignmentStrategy'];

        self::assertSame('EURO', $strategy['licensePlateNumberComponents']['usageType']);
    }

    public function test_constructor_rejects_invalid_email(): void
    {
        $this->expectException(DropshippingException::class);

        new VehicleRegistrationRequest(
            email: 'not-an-email',
            customization: $this->createCustomization(),
            vehicleHolder: $this->createVehicleHolder(),
        );
    }

    public function test_constructor_rejects_too_long_externalOrderId(): void
    {
        $this->expectException(DropshippingException::class);

        new VehicleRegistrationRequest(
            email: 'test@example.com',
            customization: $this->createCustomization(),
            vehicleHolder: $this->createVehicleHolder(),
            externalOrderId: str_repeat('a', 101),
        );
    }

    private function createRequest(
        ?string $externalOrderId = null,
        ?string $gksConfigurationId = null,
        ?string $contractPartnerKopaKey = null,
    ): VehicleRegistrationRequest {
        return new VehicleRegistrationRequest(
            email: 'test@example.com',
            customization: $this->createCustomization(),
            vehicleHolder: $this->createVehicleHolder(),
            externalOrderId: $externalOrderId,
            gksConfigurationId: $gksConfigurationId,
            contractPartnerKopaKey: $contractPartnerKopaKey,
        );
    }

    private function createCustomization(): VehicleRegistrationCustomization
    {
        return new VehicleRegistrationCustomization(
            licensePlateNumberAssignmentStrategy: new VehicleRegistrationLicensePlateNumberAssignmentStrategyRandom(
                licensePlateType: VehicleRegistrationLicensePlateType::Regular,
            ),
            vehicleRegistrationServiceTypeCode: VehicleRegistrationServiceTypeCode::NZ,
            deregistered: false,
            vehicleType: VehicleRegistrationVehicleType::Car,
            electronicInsuranceConfirmationNumber: 'ABC1234',
            vehicleIdentificationNumber: 'WBA12345678901234',
            vehicleTitleSecurityCode: 'ABCDEF123456',
            iban: 'DE89370400440532013000',
            bic: 'COBADEFFXXX',
        );
    }

    private function createVehicleHolder(): VehicleRegistrationVehicleHolder
    {
        return new VehicleRegistrationVehicleHolder(
            address: $this->createAddress(),
            placeOfBirth: 'Berlin',
            birthDate: '1990-01-31',
        );
    }

    private function createAddress(): Address
    {
        return new Address('Max', 'Mustermann', Gender::Male, 'Str', '1', '12345', 'Berlin', 'DE');
    }
}
