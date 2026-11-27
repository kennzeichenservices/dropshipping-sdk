<?php

declare(strict_types=1);

namespace Dropshipping\Tests\Unit\DTO\Requests;

use Dropshipping\DS;
use Dropshipping\DTO\Address;
use Dropshipping\DTO\Requests\VehicleRegistrationLegalPersonDurablePowerOfAttorneyReferencePersonData;
use Dropshipping\DTO\Requests\VehicleRegistrationLegalPersonProvidedPersonData;
use Dropshipping\DTO\Requests\VehicleRegistrationLegalPersonVehicleHolder;
use Dropshipping\DTO\Requests\VehicleRegistrationNaturalPersonVehicleHolder;
use Dropshipping\DTO\Requests\VehicleRegistrationVehicleHolder;
use Dropshipping\DTO\Requests\VehicleRegistrationVehicleHolderInterface;
use Dropshipping\Enums\Gender;
use Dropshipping\Enums\IdentityVerificationDocumentType;
use Dropshipping\Enums\VehicleRegistrationLegalPersonPersonDataType;
use Dropshipping\Enums\VehicleRegistrationVehicleHolderType;
use Dropshipping\Exceptions\DropshippingException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * The holder variants API 2.7.0 brought about: the pre-2.7.0 holder that sends
 * no discriminator, the natural person that does, and the legal person whose
 * data is either sent along or taken from a durable power of attorney.
 */
final class VehicleRegistrationVehicleHolderVariantsTest extends TestCase
{
    private const POWER_OF_ATTORNEY_ID = '11111111-1111-1111-1111-111111111111';

    /**
     * Every variant has to answer the interface accessors, so a caller can read
     * a holder without knowing which one it holds.
     */
    public function test_all_variants_answer_the_interface_accessors(): void
    {
        /** @var list<VehicleRegistrationVehicleHolderInterface> $holders */
        $holders = [$this->legacyHolder(), $this->naturalPerson(), $this->legalPerson(), $this->durablePowerOfAttorney()];

        self::assertSame(
            ['München', 'München', 'München', null],
            array_map(static fn (VehicleRegistrationVehicleHolderInterface $holder): ?string => $holder->address()?->cityName, $holders),
        );

        foreach ($holders as $holder) {
            self::assertNotSame([], $holder->toArray());
            self::assertNull($holder->customsMajorCustomerId());
        }

        self::assertSame(
            [null, VehicleRegistrationVehicleHolderType::NaturalPerson, VehicleRegistrationVehicleHolderType::LegalPerson, VehicleRegistrationVehicleHolderType::LegalPerson],
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

    public function test_the_natural_person_holder_sends_type_and_identity_document(): void
    {
        $holder = $this->naturalPerson();

        self::assertSame(VehicleRegistrationVehicleHolderType::NaturalPerson, $holder->holderType());
        self::assertSame([
            'type' => 'NATURAL_PERSON',
            'identityVerificationDocumentType' => 'GERMAN_ID',
            'address' => $this->address()->toArray(),
            'placeOfBirth' => 'Berlin',
            'birthDate' => '1990-01-31',
            'iban' => 'DE89370400440532013000',
        ], $holder->toArray());
    }

    public function test_the_natural_person_holder_includes_the_optional_fields_when_set(): void
    {
        $array = $this->naturalPerson(birthName: 'Musterfrau', bic: 'COBADEFFXXX')->toArray();

        self::assertSame('Musterfrau', $array['birthName']);
        self::assertSame('COBADEFFXXX', $array['bic']);
    }

    /**
     * HA needs no IBAN, so the holder has to be buildable without one. Whether
     * the code at hand needs it is checked by the request.
     */
    public function test_the_natural_person_holder_omits_iban_bic_and_birthName_when_null(): void
    {
        $array = $this->naturalPerson(iban: null)->toArray();

        self::assertArrayNotHasKey('birthName', $array);
        self::assertArrayNotHasKey('iban', $array);
        self::assertArrayNotHasKey('bic', $array);
    }

    public function test_the_natural_person_holder_validates_birth_details_like_the_old_one(): void
    {
        $rejected = 0;

        foreach ([['', '1990-01-31'], [str_repeat('a', 151), '1990-01-31'], ['Berlin', '']] as [$placeOfBirth, $birthDate]) {
            try {
                new VehicleRegistrationNaturalPersonVehicleHolder(
                    address: $this->address(),
                    placeOfBirth: $placeOfBirth,
                    birthDate: $birthDate,
                    identityVerificationDocumentType: IdentityVerificationDocumentType::GermanId,
                );

                self::fail(sprintf('Expected "%s" / "%s" to be rejected', $placeOfBirth, $birthDate));
            } catch (DropshippingException) {
                ++$rejected;
            }
        }

        self::assertSame(3, $rejected);
    }

    #[DataProvider('invalidIbanAndBic')]
    public function test_the_natural_person_holder_rejects_an_invalid_iban_or_bic(?string $iban, ?string $bic, string $message): void
    {
        $this->expectException(DropshippingException::class);
        $this->expectExceptionMessage($message);

        $this->naturalPerson(iban: $iban, bic: $bic);
    }

    public function test_the_natural_person_holder_accepts_a_swiss_iban_with_bic(): void
    {
        self::assertSame('UBSWCHZH80A', $this->naturalPerson(iban: 'CH9300762011623852957', bic: 'UBSWCHZH80A')->bic);
    }

    public function test_neither_natural_person_variant_can_carry_a_customs_id(): void
    {
        self::assertNull($this->legacyHolder()->customsMajorCustomerId());
        self::assertNull($this->naturalPerson()->customsMajorCustomerId());
    }

    public function test_the_legal_person_holder_wraps_the_provided_person_data(): void
    {
        $holder = $this->legalPerson();

        self::assertSame(VehicleRegistrationVehicleHolderType::LegalPerson, $holder->holderType());
        self::assertSame([
            'type' => 'LEGAL_PERSON',
            'personData' => [
                'type' => 'PROVIDED',
                'name' => 'Autohaus Muster GmbH',
                'companyNumber' => 'HRB 123456',
                'industryCode' => 'G01',
                'iban' => 'DE89370400440532013000',
                'address' => $this->address()->toArray(),
            ],
        ], $holder->toArray());
    }

    public function test_the_provided_person_data_carries_the_customs_id_instead_of_an_iban(): void
    {
        $holder = $this->legalPerson(customsMajorCustomerId: '123A4567', iban: null);
        $personData = $holder->toArray()['personData'];

        self::assertSame('123A4567', $personData['customsMajorCustomerId']);
        self::assertArrayNotHasKey('iban', $personData);
        self::assertArrayNotHasKey('bic', $personData);
        self::assertSame('123A4567', $holder->customsMajorCustomerId());
    }

    public function test_the_provided_person_data_includes_the_bic_when_set(): void
    {
        self::assertSame('COBADEFFXXX', $this->legalPerson(bic: 'COBADEFFXXX')->toArray()['personData']['bic']);
    }

    /**
     * A legal person cannot order HA, and HA is the only code without an IBAN,
     * so the person data needs either the IBAN or the customs ID that replaces
     * it.
     */
    public function test_the_provided_person_data_requires_an_iban_without_customs_id(): void
    {
        $this->expectException(DropshippingException::class);
        $this->expectExceptionMessage('Field "personData.iban" is required unless personData.customsMajorCustomerId is set');

        $this->legalPerson(iban: null);
    }

    public function test_the_provided_person_data_rejects_an_iban_next_to_a_customs_id(): void
    {
        $this->expectException(DropshippingException::class);
        $this->expectExceptionMessage('Field "personData.iban" must be null when personData.customsMajorCustomerId is set');

        $this->legalPerson(customsMajorCustomerId: '123A4567');
    }

    public function test_the_provided_person_data_rejects_a_bic_next_to_a_customs_id(): void
    {
        $this->expectException(DropshippingException::class);
        $this->expectExceptionMessage('Field "personData.bic" must be null when personData.customsMajorCustomerId is set');

        $this->legalPerson(customsMajorCustomerId: '123A4567', iban: null, bic: 'COBADEFFXXX');
    }

    public function test_the_provided_person_data_requires_a_bic_for_a_monegasque_iban(): void
    {
        $this->expectException(DropshippingException::class);
        $this->expectExceptionMessage('Field "personData.bic" is required for an iban starting with MC');

        $this->legalPerson(iban: 'MC5811222000010123456789030');
    }

    public function test_the_provided_person_data_rejects_an_iban_of_the_wrong_length(): void
    {
        $this->expectException(DropshippingException::class);
        $this->expectExceptionMessage('personData.iban');

        $this->legalPerson(iban: 'DE123');
    }

    public function test_the_provided_person_data_rejects_an_industry_code_of_the_wrong_shape(): void
    {
        foreach (['G1', 'G001', 'g01', 'G-1', '', 'GÖ1', "G01\n"] as $industryCode) {
            try {
                $this->legalPerson(industryCode: $industryCode);

                self::fail(sprintf('Expected the industry code "%s" to be rejected', $industryCode));
            } catch (DropshippingException $exception) {
                self::assertStringContainsString('Field "personData.industryCode" must be three characters of A-Z and 0-9', $exception->getMessage());
            }
        }
    }

    public function test_the_provided_person_data_accepts_a_code_of_digits_only(): void
    {
        $personData = $this->legalPerson(industryCode: '012')->personData;

        self::assertInstanceOf(VehicleRegistrationLegalPersonProvidedPersonData::class, $personData);
        self::assertSame('012', $personData->industryCode);
    }

    public function test_the_provided_person_data_rejects_a_blank_company_number(): void
    {
        $this->expectException(DropshippingException::class);
        $this->expectExceptionMessage('Field "personData.companyNumber" must not be blank');

        $this->legalPerson(companyNumber: "  \t ");
    }

    public function test_the_provided_person_data_rejects_an_empty_company_number(): void
    {
        $this->expectException(DropshippingException::class);

        $this->legalPerson(companyNumber: '');
    }

    public function test_the_provided_person_data_rejects_a_too_long_company_number(): void
    {
        $this->expectException(DropshippingException::class);

        $this->legalPerson(companyNumber: str_repeat('a', 101));
    }

    public function test_the_provided_person_data_rejects_an_empty_name(): void
    {
        $this->expectException(DropshippingException::class);

        $this->legalPerson(name: '');
    }

    public function test_the_provided_person_data_rejects_a_too_long_name(): void
    {
        $this->expectException(DropshippingException::class);

        $this->legalPerson(name: str_repeat('a', 256));
    }

    public function test_the_provided_person_data_rejects_a_customs_id_of_the_wrong_shape(): void
    {
        foreach (['12A4567', '1234A567', '123a4567', '123A456', '123A45678', 'ABC12345', ''] as $customsMajorCustomerId) {
            try {
                $this->legalPerson(customsMajorCustomerId: $customsMajorCustomerId, iban: null);

                self::fail(sprintf('Expected the customs ID "%s" to be rejected', $customsMajorCustomerId));
            } catch (DropshippingException $exception) {
                self::assertStringContainsString(
                    'Field "personData.customsMajorCustomerId" must be three digits, an upper case letter and four digits',
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

        $personData = (new VehicleRegistrationLegalPersonProvidedPersonData(
            address: $address,
            name: 'Autohaus Muster GmbH',
            industryCode: 'G01',
            companyNumber: 'HRB 123456',
            iban: 'DE89370400440532013000',
        ))->toArray();

        self::assertSame('Autohaus Muster GmbH', $personData['name']);
        self::assertSame('Muster Holding AG', $personData['address']['companyName']);
    }

    public function test_the_legal_person_holder_exposes_the_address_of_its_person_data(): void
    {
        self::assertSame('München', $this->legalPerson()->address()?->cityName);
    }

    public function test_the_durable_power_of_attorney_reference_sends_type_and_id_only(): void
    {
        $holder = $this->durablePowerOfAttorney();

        self::assertSame(VehicleRegistrationVehicleHolderType::LegalPerson, $holder->holderType());
        self::assertSame([
            'type' => 'LEGAL_PERSON',
            'personData' => [
                'type' => 'DURABLE_POWER_OF_ATTORNEY_REFERENCE',
                'durablePowerOfAttorneyId' => self::POWER_OF_ATTORNEY_ID,
            ],
        ], $holder->toArray());
    }

    public function test_the_durable_power_of_attorney_reference_has_neither_address_nor_customs_id(): void
    {
        $holder = $this->durablePowerOfAttorney();

        self::assertNull($holder->address());
        self::assertNull($holder->customsMajorCustomerId());
        self::assertSame(VehicleRegistrationLegalPersonPersonDataType::DurablePowerOfAttorneyReference, $holder->personData->type());
    }

    public function test_the_durable_power_of_attorney_reference_accepts_an_upper_case_uuid(): void
    {
        $personData = new VehicleRegistrationLegalPersonDurablePowerOfAttorneyReferencePersonData('ABCDEF01-2345-6789-ABCD-EF0123456789');

        self::assertSame('ABCDEF01-2345-6789-ABCD-EF0123456789', $personData->toArray()['durablePowerOfAttorneyId']);
    }

    #[DataProvider('invalidDurablePowerOfAttorneyIds')]
    public function test_the_durable_power_of_attorney_reference_rejects_anything_but_a_uuid(string $id): void
    {
        $this->expectException(DropshippingException::class);
        $this->expectExceptionMessage('Field "personData.durablePowerOfAttorneyId" must be a UUID');

        new VehicleRegistrationLegalPersonDurablePowerOfAttorneyReferencePersonData($id);
    }

    public function test_the_factories_build_the_same_holders(): void
    {
        self::assertSame(
            $this->naturalPerson(birthName: 'Musterfrau', bic: 'COBADEFFXXX')->toArray(),
            DS::registrationNaturalPersonVehicleHolder(
                $this->address(),
                'Berlin',
                '1990-01-31',
                IdentityVerificationDocumentType::GermanId,
                'Musterfrau',
                'DE89370400440532013000',
                'COBADEFFXXX',
            )->toArray(),
        );
        self::assertSame(
            $this->legalPerson(customsMajorCustomerId: '123A4567', iban: null)->toArray(),
            DS::registrationLegalPersonVehicleHolder($this->address(), 'Autohaus Muster GmbH', 'G01', 'HRB 123456', '123A4567')->toArray(),
        );
        self::assertSame(
            $this->durablePowerOfAttorney()->toArray(),
            DS::registrationLegalPersonVehicleHolderFromDurablePowerOfAttorney(self::POWER_OF_ATTORNEY_ID)->toArray(),
        );
    }

    /**
     * @return iterable<string, array{string|null, string|null, string}>
     */
    public static function invalidIbanAndBic(): iterable
    {
        yield 'iban too short' => ['DE893704004405', null, 'vehicleHolder.iban'];
        yield 'iban too long' => [str_repeat('D', 35), null, 'vehicleHolder.iban'];
        yield 'bic too short' => ['DE89370400440532013000', 'COBADEF', 'vehicleHolder.bic'];
        yield 'bic too long' => ['DE89370400440532013000', 'COBADEFFXXXX', 'vehicleHolder.bic'];
        yield 'swiss iban without bic' => ['CH9300762011623852957', null, 'Field "vehicleHolder.bic" is required for an iban starting with CH'];
        yield 'lower case san marino iban without bic' => ['sm86U0322509800000000270100', null, 'Field "vehicleHolder.bic" is required for an iban starting with sm'];
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function invalidDurablePowerOfAttorneyIds(): iterable
    {
        yield 'empty' => [''];
        yield 'blank' => ['   '];
        yield 'order id' => ['12345'];
        yield 'without dashes' => ['11111111111111111111111111111111'];
        yield 'trailing newline' => ["11111111-1111-1111-1111-111111111111\n"];
        yield 'surrounding spaces' => [' 11111111-1111-1111-1111-111111111111 '];
        yield 'braces' => ['{11111111-1111-1111-1111-111111111111}'];
        yield 'non-hex' => ['gggggggg-1111-1111-1111-111111111111'];
        yield 'huge' => [str_repeat('1', 100_000)];
    }

    private function legacyHolder(): VehicleRegistrationVehicleHolder
    {
        return new VehicleRegistrationVehicleHolder(
            address: $this->address(),
            placeOfBirth: 'Berlin',
            birthDate: '1990-01-31',
        );
    }

    private function naturalPerson(
        ?string $birthName = null,
        ?string $iban = 'DE89370400440532013000',
        ?string $bic = null,
    ): VehicleRegistrationNaturalPersonVehicleHolder {
        return new VehicleRegistrationNaturalPersonVehicleHolder(
            address: $this->address(),
            placeOfBirth: 'Berlin',
            birthDate: '1990-01-31',
            identityVerificationDocumentType: IdentityVerificationDocumentType::GermanId,
            birthName: $birthName,
            iban: $iban,
            bic: $bic,
        );
    }

    private function legalPerson(
        string $name = 'Autohaus Muster GmbH',
        string $industryCode = 'G01',
        string $companyNumber = 'HRB 123456',
        ?string $customsMajorCustomerId = null,
        ?string $iban = 'DE89370400440532013000',
        ?string $bic = null,
    ): VehicleRegistrationLegalPersonVehicleHolder {
        return new VehicleRegistrationLegalPersonVehicleHolder(
            personData: new VehicleRegistrationLegalPersonProvidedPersonData(
                address: $this->address(),
                name: $name,
                industryCode: $industryCode,
                companyNumber: $companyNumber,
                customsMajorCustomerId: $customsMajorCustomerId,
                iban: $iban,
                bic: $bic,
            ),
        );
    }

    private function durablePowerOfAttorney(): VehicleRegistrationLegalPersonVehicleHolder
    {
        return new VehicleRegistrationLegalPersonVehicleHolder(
            personData: new VehicleRegistrationLegalPersonDurablePowerOfAttorneyReferencePersonData(self::POWER_OF_ATTORNEY_ID),
        );
    }

    private function address(): Address
    {
        return new Address('Max', 'Mustermann', Gender::Male, 'Autohausstraße', '7a', '80331', 'München', 'DE');
    }
}
