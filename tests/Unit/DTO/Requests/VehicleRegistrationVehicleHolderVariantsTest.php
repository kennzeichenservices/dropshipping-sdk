<?php

declare(strict_types=1);

namespace Dropshipping\Tests\Unit\DTO\Requests;

use Dropshipping\DTO\Address;
use Dropshipping\DTO\Requests\VehicleRegistrationLegalPersonVehicleHolder;
use Dropshipping\DTO\Requests\VehicleRegistrationNaturalPersonVehicleHolder;
use Dropshipping\DTO\Requests\VehicleRegistrationVehicleHolder;
use Dropshipping\DTO\Requests\VehicleRegistrationVehicleHolderInterface;
use Dropshipping\Enums\Gender;
use Dropshipping\Enums\VehicleRegistrationVehicleHolderType;
use Dropshipping\Exceptions\DropshippingException;
use PHPUnit\Framework\TestCase;

/**
 * The three holder variants API 2.7.0 brought about: the pre-2.7.0 holder that
 * sends no discriminator, and the natural and legal person that do.
 */
final class VehicleRegistrationVehicleHolderVariantsTest extends TestCase
{
    /**
     * Every variant has to answer the three interface accessors, so a caller can
     * read a holder without knowing which one it holds.
     */
    public function test_all_three_variants_answer_the_interface_accessors(): void
    {
        /** @var list<VehicleRegistrationVehicleHolderInterface> $holders */
        $holders = [$this->legacyHolder(), $this->naturalPerson(), $this->legalPerson()];

        foreach ($holders as $holder) {
            self::assertSame('München', $holder->address()->cityName);
            self::assertNotSame([], $holder->toArray());
            self::assertNull($holder->customsMajorCustomerId());
        }

        self::assertSame(
            [null, VehicleRegistrationVehicleHolderType::NaturalPerson, VehicleRegistrationVehicleHolderType::LegalPerson],
            array_map(
                static fn (VehicleRegistrationVehicleHolderInterface $holder): ?VehicleRegistrationVehicleHolderType => $holder->holderType(),
                $holders,
            ),
        );
    }

    /**
     * The whole point of keeping the old class: an integration on API 2.4.0 to
     * 2.6.0 must keep sending the payload it sent before the SDK learned about
     * the discriminator.
     */
    public function test_the_pre_2_7_0_holder_sends_no_type(): void
    {
        $holder = $this->legacyHolder();

        self::assertNull($holder->holderType());
        self::assertSame(['address', 'placeOfBirth', 'birthDate'], array_keys($holder->toArray()));
    }

    public function test_the_natural_person_holder_sends_its_type_first(): void
    {
        $holder = $this->naturalPerson();

        self::assertSame(VehicleRegistrationVehicleHolderType::NaturalPerson, $holder->holderType());
        self::assertSame(
            ['type', 'address', 'placeOfBirth', 'birthDate'],
            array_keys($holder->toArray()),
        );
        self::assertSame('NATURAL_PERSON', $holder->toArray()['type']);
    }

    public function test_the_natural_person_holder_carries_the_same_values_as_the_old_one(): void
    {
        $legacy = $this->legacyHolder()->toArray();
        $current = $this->naturalPerson()->toArray();

        unset($current['type']);

        self::assertSame($legacy, $current);
    }

    public function test_the_natural_person_holder_includes_birthName_when_set(): void
    {
        $holder = new VehicleRegistrationNaturalPersonVehicleHolder(
            address: $this->address(),
            placeOfBirth: 'Berlin',
            birthDate: '1990-01-31',
            birthName: 'Musterfrau',
        );

        self::assertSame('Musterfrau', $holder->toArray()['birthName']);
    }

    public function test_the_natural_person_holder_omits_birthName_when_null(): void
    {
        self::assertArrayNotHasKey('birthName', $this->naturalPerson()->toArray());
    }

    public function test_the_natural_person_holder_validates_like_the_old_one(): void
    {
        $rejected = 0;

        foreach ([['', '1990-01-31'], [str_repeat('a', 151), '1990-01-31'], ['Berlin', '']] as [$placeOfBirth, $birthDate]) {
            try {
                new VehicleRegistrationNaturalPersonVehicleHolder(
                    address: $this->address(),
                    placeOfBirth: $placeOfBirth,
                    birthDate: $birthDate,
                );

                self::fail(sprintf('Expected "%s" / "%s" to be rejected', $placeOfBirth, $birthDate));
            } catch (DropshippingException) {
                ++$rejected;
            }
        }

        self::assertSame(3, $rejected);
    }

    public function test_neither_natural_person_variant_can_carry_a_customs_id(): void
    {
        self::assertNull($this->legacyHolder()->customsMajorCustomerId());
        self::assertNull($this->naturalPerson()->customsMajorCustomerId());
    }

    public function test_the_legal_person_holder_sends_the_company_fields(): void
    {
        $holder = $this->legalPerson();

        self::assertSame(VehicleRegistrationVehicleHolderType::LegalPerson, $holder->holderType());
        self::assertSame([
            'type' => 'LEGAL_PERSON',
            'address' => $this->address()->toArray(),
            'name' => 'Autohaus Muster GmbH',
            'industryCode' => 'G01',
            'companyNumber' => 'HRB 123456',
        ], $holder->toArray());
    }

    public function test_the_legal_person_holder_includes_the_customs_id_when_set(): void
    {
        $holder = $this->legalPerson(customsMajorCustomerId: '123A4567');

        self::assertSame('123A4567', $holder->toArray()['customsMajorCustomerId']);
        self::assertSame('123A4567', $holder->customsMajorCustomerId());
    }

    public function test_the_legal_person_holder_omits_the_customs_id_when_null(): void
    {
        self::assertArrayNotHasKey('customsMajorCustomerId', $this->legalPerson()->toArray());
    }

    public function test_the_legal_person_holder_rejects_an_industry_code_of_the_wrong_shape(): void
    {
        foreach (['G1', 'G001', 'g01', 'G-1', '', 'GÖ1'] as $industryCode) {
            try {
                $this->legalPerson(industryCode: $industryCode);

                self::fail(sprintf('Expected the industry code "%s" to be rejected', $industryCode));
            } catch (DropshippingException $exception) {
                self::assertStringContainsString('Field "industryCode" must be three characters of A-Z and 0-9', $exception->getMessage());
            }
        }
    }

    public function test_the_legal_person_holder_accepts_a_code_of_digits_only(): void
    {
        self::assertSame('012', $this->legalPerson(industryCode: '012')->industryCode);
    }

    public function test_the_legal_person_holder_rejects_a_blank_company_number(): void
    {
        $this->expectException(DropshippingException::class);
        $this->expectExceptionMessage('Field "companyNumber" must not be blank');

        $this->legalPerson(companyNumber: "  \t ");
    }

    public function test_the_legal_person_holder_rejects_an_empty_company_number(): void
    {
        $this->expectException(DropshippingException::class);

        $this->legalPerson(companyNumber: '');
    }

    public function test_the_legal_person_holder_rejects_a_too_long_company_number(): void
    {
        $this->expectException(DropshippingException::class);

        $this->legalPerson(companyNumber: str_repeat('a', 101));
    }

    public function test_the_legal_person_holder_rejects_an_empty_name(): void
    {
        $this->expectException(DropshippingException::class);

        $this->legalPerson(name: '');
    }

    public function test_the_legal_person_holder_rejects_a_too_long_name(): void
    {
        $this->expectException(DropshippingException::class);

        $this->legalPerson(name: str_repeat('a', 256));
    }

    public function test_the_legal_person_holder_rejects_a_customs_id_of_the_wrong_shape(): void
    {
        foreach (['12A4567', '1234A567', '123a4567', '123A456', '123A45678', 'ABC12345', ''] as $customsMajorCustomerId) {
            try {
                $this->legalPerson(customsMajorCustomerId: $customsMajorCustomerId);

                self::fail(sprintf('Expected the customs ID "%s" to be rejected', $customsMajorCustomerId));
            } catch (DropshippingException $exception) {
                self::assertStringContainsString(
                    'Field "customsMajorCustomerId" must be three digits, an upper case letter and four digits',
                    $exception->getMessage(),
                );
            }
        }
    }

    /**
     * The address of a legal person names a contact person, and its companyName
     * is not what the API reads as the holder's name, so the two are allowed to
     * differ and both end up in the payload.
     */
    public function test_the_legal_person_name_is_independent_of_the_address_company_name(): void
    {
        $address = new Address(
            firstName: 'Max',
            lastName: 'Mustermann',
            gender: Gender::Male,
            streetName: 'Autohausstraße',
            houseNumber: '7a',
            zipCode: '80331',
            cityName: 'München',
            countryCode: 'DE',
            companyName: 'Muster Holding AG',
        );

        $array = (new VehicleRegistrationLegalPersonVehicleHolder(
            address: $address,
            name: 'Autohaus Muster GmbH',
            industryCode: 'G01',
            companyNumber: 'HRB 123456',
        ))->toArray();

        self::assertSame('Autohaus Muster GmbH', $array['name']);
        self::assertSame('Muster Holding AG', $array['address']['companyName']);
    }

    public function test_the_legal_person_holder_exposes_its_address(): void
    {
        self::assertSame('München', $this->legalPerson()->address()->cityName);
    }

    private function legacyHolder(): VehicleRegistrationVehicleHolder
    {
        return new VehicleRegistrationVehicleHolder(
            address: $this->address(),
            placeOfBirth: 'Berlin',
            birthDate: '1990-01-31',
        );
    }

    private function naturalPerson(): VehicleRegistrationNaturalPersonVehicleHolder
    {
        return new VehicleRegistrationNaturalPersonVehicleHolder(
            address: $this->address(),
            placeOfBirth: 'Berlin',
            birthDate: '1990-01-31',
        );
    }

    private function legalPerson(
        string $name = 'Autohaus Muster GmbH',
        string $industryCode = 'G01',
        string $companyNumber = 'HRB 123456',
        ?string $customsMajorCustomerId = null,
    ): VehicleRegistrationLegalPersonVehicleHolder {
        return new VehicleRegistrationLegalPersonVehicleHolder(
            address: $this->address(),
            name: $name,
            industryCode: $industryCode,
            companyNumber: $companyNumber,
            customsMajorCustomerId: $customsMajorCustomerId,
        );
    }

    private function address(): Address
    {
        return new Address('Max', 'Mustermann', Gender::Male, 'Autohausstraße', '7a', '80331', 'München', 'DE');
    }
}
