<?php

declare(strict_types=1);

namespace Dropshipping\Tests\Unit\DTO;

use Dropshipping\DTO\VehicleRegistrationDeliveryConfigurationAddress;
use Dropshipping\DTO\VehicleRegistrationDeliveryConfigurationLegalPersonRecipient;
use Dropshipping\DTO\VehicleRegistrationDeliveryConfigurationNaturalPersonRecipient;
use Dropshipping\DTO\VehicleRegistrationDeliveryConfigurations;
use Dropshipping\DTO\VehicleRegistrationDeliveryToOwnerDeliveryConfiguration;
use Dropshipping\DTO\VehicleRegistrationDeliveryToThirdPartyDeliveryConfiguration;
use Dropshipping\DTO\VehicleRegistrationPickupByThirdPartyDeliveryConfiguration;
use Dropshipping\Enums\Gender;
use Dropshipping\Enums\VehicleRegistrationDeliveryConfigurationDeliveryOption;
use Dropshipping\Enums\VehicleRegistrationDeliveryConfigurationRecipientType;
use Dropshipping\Exceptions\DropshippingException;
use PHPUnit\Framework\TestCase;

final class VehicleRegistrationDeliveryConfigurationsTest extends TestCase
{
    public function test_address_serializes_every_field(): void
    {
        self::assertSame([
            'streetName' => 'Musterstraße',
            'houseNumber' => '1',
            'zipCode' => '12345',
            'cityName' => 'Berlin',
        ], $this->address()->toArray());
    }

    public function test_address_rejects_an_empty_streetName(): void
    {
        $this->expectException(DropshippingException::class);
        $this->expectExceptionMessage('Field "streetName" must be between 1 and 100 characters');

        new VehicleRegistrationDeliveryConfigurationAddress(
            streetName: '',
            houseNumber: '1',
            zipCode: '12345',
            cityName: 'Berlin',
        );
    }

    public function test_address_rejects_a_too_long_houseNumber(): void
    {
        $this->expectException(DropshippingException::class);

        new VehicleRegistrationDeliveryConfigurationAddress(
            streetName: 'Musterstraße',
            houseNumber: '12345678901',
            zipCode: '12345',
            cityName: 'Berlin',
        );
    }

    public function test_natural_person_recipient_serializes_with_its_discriminator(): void
    {
        $recipient = new VehicleRegistrationDeliveryConfigurationNaturalPersonRecipient(
            address: $this->address(),
            firstName: 'Erika',
            lastName: 'Mustermann',
            gender: Gender::Female,
            birthDate: '1985-07-14',
        );

        self::assertSame(
            VehicleRegistrationDeliveryConfigurationRecipientType::NaturalPerson,
            $recipient->type(),
        );
        self::assertSame($this->address()->toArray(), $recipient->address()->toArray());
        self::assertSame([
            'type' => 'NATURAL_PERSON',
            'address' => [
                'streetName' => 'Musterstraße',
                'houseNumber' => '1',
                'zipCode' => '12345',
                'cityName' => 'Berlin',
            ],
            'firstName' => 'Erika',
            'lastName' => 'Mustermann',
            'gender' => 'FEMALE',
            'birthDate' => '1985-07-14',
        ], $recipient->toArray());
    }

    public function test_natural_person_recipient_rejects_an_empty_birthDate(): void
    {
        $this->expectException(DropshippingException::class);
        $this->expectExceptionMessage('Field "birthDate" must not be empty');

        new VehicleRegistrationDeliveryConfigurationNaturalPersonRecipient(
            address: $this->address(),
            firstName: 'Erika',
            lastName: 'Mustermann',
            gender: Gender::Female,
            birthDate: '',
        );
    }

    public function test_legal_person_recipient_serializes_with_its_discriminator(): void
    {
        $recipient = $this->company();

        self::assertSame(
            VehicleRegistrationDeliveryConfigurationRecipientType::LegalPerson,
            $recipient->type(),
        );
        self::assertSame([
            'type' => 'LEGAL_PERSON',
            'address' => [
                'streetName' => 'Musterstraße',
                'houseNumber' => '1',
                'zipCode' => '12345',
                'cityName' => 'Berlin',
            ],
            'name' => 'Autohaus Muster GmbH',
        ], $recipient->toArray());
    }

    public function test_legal_person_recipient_rejects_a_too_long_name(): void
    {
        $this->expectException(DropshippingException::class);

        new VehicleRegistrationDeliveryConfigurationLegalPersonRecipient(
            address: $this->address(),
            name: str_repeat('a', 101),
        );
    }

    public function test_delivery_to_owner_serializes_to_the_discriminator_only(): void
    {
        $configuration = new VehicleRegistrationDeliveryToOwnerDeliveryConfiguration();

        self::assertSame(
            VehicleRegistrationDeliveryConfigurationDeliveryOption::DeliveryToOwner,
            $configuration->deliveryOption(),
        );
        self::assertSame(['deliveryOption' => 'DELIVERY_TO_OWNER'], $configuration->toArray());
    }

    public function test_delivery_to_third_party_carries_its_recipient(): void
    {
        $configuration = new VehicleRegistrationDeliveryToThirdPartyDeliveryConfiguration($this->company());

        self::assertSame(
            VehicleRegistrationDeliveryConfigurationDeliveryOption::DeliveryToThirdParty,
            $configuration->deliveryOption(),
        );

        $array = $configuration->toArray();

        self::assertSame('DELIVERY_TO_THIRD_PARTY', $array['deliveryOption']);
        self::assertSame($this->company()->toArray(), $array['recipient']);
    }

    public function test_pickup_by_third_party_carries_its_recipient(): void
    {
        $configuration = new VehicleRegistrationPickupByThirdPartyDeliveryConfiguration($this->company());

        self::assertSame(
            VehicleRegistrationDeliveryConfigurationDeliveryOption::PickupByThirdParty,
            $configuration->deliveryOption(),
        );

        $array = $configuration->toArray();

        self::assertSame('PICKUP_BY_THIRD_PARTY', $array['deliveryOption']);
        self::assertSame($this->company()->toArray(), $array['recipient']);
    }

    public function test_configurations_serialize_both_documents(): void
    {
        $configurations = new VehicleRegistrationDeliveryConfigurations(
            vehicleRegistrationCertificate: new VehicleRegistrationDeliveryToOwnerDeliveryConfiguration(),
            vehicleTitle: new VehicleRegistrationPickupByThirdPartyDeliveryConfiguration($this->company()),
        );

        $array = $configurations->toArray();

        self::assertSame(['vehicleRegistrationCertificate', 'vehicleTitle'], array_keys($array));
        self::assertSame('DELIVERY_TO_OWNER', $array['vehicleRegistrationCertificate']['deliveryOption']);
        self::assertSame('PICKUP_BY_THIRD_PARTY', $array['vehicleTitle']['deliveryOption']);
    }

    private function address(): VehicleRegistrationDeliveryConfigurationAddress
    {
        return new VehicleRegistrationDeliveryConfigurationAddress(
            streetName: 'Musterstraße',
            houseNumber: '1',
            zipCode: '12345',
            cityName: 'Berlin',
        );
    }

    private function company(): VehicleRegistrationDeliveryConfigurationLegalPersonRecipient
    {
        return new VehicleRegistrationDeliveryConfigurationLegalPersonRecipient(
            address: $this->address(),
            name: 'Autohaus Muster GmbH',
        );
    }
}
