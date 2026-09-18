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
            vehicleTitle: new VehicleRegistrationDeliveryToThirdPartyDeliveryConfiguration($this->company()),
        );

        $array = $configurations->toArray();

        self::assertSame(['vehicleRegistrationCertificate', 'vehicleTitle'], array_keys($array));
        self::assertSame('DELIVERY_TO_OWNER', $array['vehicleRegistrationCertificate']['deliveryOption']);
        self::assertSame('DELIVERY_TO_THIRD_PARTY', $array['vehicleTitle']['deliveryOption']);
    }

    // The pickup rule below is not in the 2.4.0 spec. It comes from API rejections — a bare
    // HTTP 400 "Validation error" naming no field — and is enforced at construction time so
    // that it fails here rather than after identification and QES have already run. See the
    // class docblock of VehicleRegistrationDeliveryConfigurations.

    public function test_pickup_of_both_documents_by_the_same_third_party_is_allowed(): void
    {
        $configurations = new VehicleRegistrationDeliveryConfigurations(
            vehicleRegistrationCertificate: new VehicleRegistrationPickupByThirdPartyDeliveryConfiguration($this->company()),
            vehicleTitle: new VehicleRegistrationPickupByThirdPartyDeliveryConfiguration($this->company()),
        );

        $array = $configurations->toArray();

        self::assertSame('PICKUP_BY_THIRD_PARTY', $array['vehicleRegistrationCertificate']['deliveryOption']);
        self::assertSame('PICKUP_BY_THIRD_PARTY', $array['vehicleTitle']['deliveryOption']);
    }

    public function test_pickup_recipients_are_compared_by_value_not_by_identity(): void
    {
        // Two separate objects carrying the same data — what building each configuration
        // from the same form input produces.
        $configurations = new VehicleRegistrationDeliveryConfigurations(
            vehicleRegistrationCertificate: new VehicleRegistrationPickupByThirdPartyDeliveryConfiguration(
                new VehicleRegistrationDeliveryConfigurationLegalPersonRecipient(
                    address: $this->address(),
                    name: 'Autohaus Muster GmbH',
                ),
            ),
            vehicleTitle: new VehicleRegistrationPickupByThirdPartyDeliveryConfiguration($this->company()),
        );

        self::assertSame('PICKUP_BY_THIRD_PARTY', $configurations->toArray()['vehicleTitle']['deliveryOption']);
    }

    public function test_configurations_reject_pickup_of_the_title_alone(): void
    {
        $this->expectException(DropshippingException::class);
        $this->expectExceptionMessage('applies to both registration documents or to neither');

        new VehicleRegistrationDeliveryConfigurations(
            vehicleRegistrationCertificate: new VehicleRegistrationDeliveryToOwnerDeliveryConfiguration(),
            vehicleTitle: new VehicleRegistrationPickupByThirdPartyDeliveryConfiguration($this->company()),
        );
    }

    public function test_configurations_reject_pickup_of_the_certificate_alone(): void
    {
        $this->expectException(DropshippingException::class);
        $this->expectExceptionMessage('vehicleRegistrationCertificate is collected');

        new VehicleRegistrationDeliveryConfigurations(
            vehicleRegistrationCertificate: new VehicleRegistrationPickupByThirdPartyDeliveryConfiguration($this->company()),
            vehicleTitle: new VehicleRegistrationDeliveryToOwnerDeliveryConfiguration(),
        );
    }

    public function test_configurations_reject_pickup_alongside_delivery_to_the_same_third_party(): void
    {
        $this->expectException(DropshippingException::class);

        new VehicleRegistrationDeliveryConfigurations(
            vehicleRegistrationCertificate: new VehicleRegistrationPickupByThirdPartyDeliveryConfiguration($this->company()),
            vehicleTitle: new VehicleRegistrationDeliveryToThirdPartyDeliveryConfiguration($this->company()),
        );
    }

    public function test_configurations_reject_pickup_by_two_different_third_parties(): void
    {
        $this->expectException(DropshippingException::class);
        $this->expectExceptionMessage('name different recipients');

        new VehicleRegistrationDeliveryConfigurations(
            vehicleRegistrationCertificate: new VehicleRegistrationPickupByThirdPartyDeliveryConfiguration($this->company()),
            vehicleTitle: new VehicleRegistrationPickupByThirdPartyDeliveryConfiguration(
                new VehicleRegistrationDeliveryConfigurationLegalPersonRecipient(
                    address: $this->address(),
                    name: 'Autohaus Anders GmbH',
                ),
            ),
        );
    }

    public function test_delivery_to_third_party_is_free_of_the_pickup_restrictions(): void
    {
        // Only one document deviating, and two different recipients, are both fine here —
        // the restrictions apply to pickup alone.
        $configurations = new VehicleRegistrationDeliveryConfigurations(
            vehicleRegistrationCertificate: new VehicleRegistrationDeliveryToThirdPartyDeliveryConfiguration(
                new VehicleRegistrationDeliveryConfigurationNaturalPersonRecipient(
                    address: $this->address(),
                    firstName: 'Erika',
                    lastName: 'Mustermann',
                    gender: Gender::Female,
                    birthDate: '1985-07-14',
                ),
            ),
            vehicleTitle: new VehicleRegistrationDeliveryToThirdPartyDeliveryConfiguration($this->company()),
        );

        self::assertSame('NATURAL_PERSON', $configurations->toArray()['vehicleRegistrationCertificate']['recipient']['type']);
        self::assertSame('LEGAL_PERSON', $configurations->toArray()['vehicleTitle']['recipient']['type']);
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
