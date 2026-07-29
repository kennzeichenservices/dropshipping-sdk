<?php

declare(strict_types=1);

namespace Dropshipping\Tests\Unit\DTO\Requests;

use Dropshipping\DTO\Address;
use Dropshipping\DTO\EuroLicensePlateNumberComponents;
use Dropshipping\DTO\Requests\VehicleRegistrationRequest;
use Dropshipping\DTO\Requests\VehicleRegistrationVehicleHolder;
use Dropshipping\DTO\VehicleRegistrationCustomization;
use Dropshipping\Enums\Gender;
use Dropshipping\Enums\VehicleRegistrationLicensePlateNumberAssignmentStrategy;
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
        self::assertSame('RANDOM', $array['customization']['licensePlateNumberAssignmentStrategy']);
        self::assertSame('NZ', $array['customization']['vehicleRegistrationServiceTypeCode']);
        self::assertSame('CAR', $array['customization']['vehicleType']);
        self::assertSame('REGULAR', $array['customization']['licensePlateType']);
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

    public function test_vehicleHolder_contains_only_address(): void
    {
        $array = $this->createRequest()->toArray();

        self::assertSame(['address'], array_keys($array['vehicleHolder']));
        self::assertSame('Mustermann', $array['vehicleHolder']['address']['lastName']);
    }

    public function test_licensePlateNumberComponents_carry_euro_usage_type(): void
    {
        $array = $this->createRequest()->toArray();

        self::assertSame('EURO', $array['customization']['licensePlateNumberComponents']['usageType']);
    }

    public function test_constructor_rejects_invalid_email(): void
    {
        $this->expectException(DropshippingException::class);

        new VehicleRegistrationRequest(
            email: 'not-an-email',
            customization: $this->createCustomization(),
            vehicleHolder: new VehicleRegistrationVehicleHolder($this->createAddress()),
        );
    }

    public function test_constructor_rejects_too_long_externalOrderId(): void
    {
        $this->expectException(DropshippingException::class);

        new VehicleRegistrationRequest(
            email: 'test@example.com',
            customization: $this->createCustomization(),
            vehicleHolder: new VehicleRegistrationVehicleHolder($this->createAddress()),
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
            vehicleHolder: new VehicleRegistrationVehicleHolder($this->createAddress()),
            externalOrderId: $externalOrderId,
            gksConfigurationId: $gksConfigurationId,
            contractPartnerKopaKey: $contractPartnerKopaKey,
        );
    }

    private function createCustomization(): VehicleRegistrationCustomization
    {
        return new VehicleRegistrationCustomization(
            licensePlateNumberAssignmentStrategy: VehicleRegistrationLicensePlateNumberAssignmentStrategy::Random,
            vehicleRegistrationServiceTypeCode: VehicleRegistrationServiceTypeCode::NZ,
            licensePlateNumberComponents: new EuroLicensePlateNumberComponents('B', 'AB', '123'),
            deregistered: false,
            vehicleType: VehicleRegistrationVehicleType::Car,
            licensePlateType: VehicleRegistrationLicensePlateType::Regular,
            electronicInsuranceConfirmationNumber: 'ABC1234',
            vehicleIdentificationNumber: 'WBA12345678901234',
            vehicleTitleSecurityCode: 'ABCDEF123456',
            iban: 'DE89370400440532013000',
            bic: 'COBADEFFXXX',
        );
    }

    private function createAddress(): Address
    {
        return new Address('Max', 'Mustermann', Gender::Male, 'Str', '1', '12345', 'Berlin', 'DE');
    }
}
