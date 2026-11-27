<?php

declare(strict_types=1);

namespace Dropshipping\Tests\Unit\DTO\Requests;

use Dropshipping\DTO\Address;
use Dropshipping\DTO\EuroLicensePlateNumberComponents;
use Dropshipping\DTO\Requests\VehicleRegistrationLegalPersonDurablePowerOfAttorneyReferencePersonData;
use Dropshipping\DTO\Requests\VehicleRegistrationLegalPersonProvidedPersonData;
use Dropshipping\DTO\Requests\VehicleRegistrationLegalPersonVehicleHolder;
use Dropshipping\DTO\Requests\VehicleRegistrationNaturalPersonVehicleHolder;
use Dropshipping\DTO\Requests\VehicleRegistrationRequest;
use Dropshipping\DTO\Requests\VehicleRegistrationVehicleHolder;
use Dropshipping\DTO\VehicleRegistrationCustomization;
use Dropshipping\DTO\VehicleRegistrationLicensePlateNumberAssignmentStrategyRandom;
use Dropshipping\DTO\VehicleRegistrationLicensePlateNumberAssignmentStrategyReservation;
use Dropshipping\DTO\VehicleRegistrationLicensePlateNumberAssignmentStrategyRetained;
use Dropshipping\DTO\VehicleRegistrationPreviousLicensePlate;
use Dropshipping\Enums\Gender;
use Dropshipping\Enums\IdentityVerificationDocumentType;
use Dropshipping\Enums\VehicleRegistrationLicensePlateType;
use Dropshipping\Enums\VehicleRegistrationServiceTypeCode;
use Dropshipping\Enums\VehicleRegistrationVehicleHolderType;
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
            deregistered: true,
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

    public function test_toArray_includes_identityVerificationDocumentType_as_its_api_value(): void
    {
        $array = $this->createRequest(
            identityVerificationDocumentType: IdentityVerificationDocumentType::ForeignPapersWithProofOfAddress,
        )->toArray();

        self::assertSame('FOREIGN_PAPERS_WITH_PROOF_OF_ADDRESS', $array['identityVerificationDocumentType']);
    }

    /**
     * The field is required by API 2.6.0 and unknown to 2.4.0 and 2.5.0, so leaving
     * it unset must keep it out of the payload rather than send a null the older
     * versions would reject.
     */
    public function test_toArray_omits_identityVerificationDocumentType_when_not_set(): void
    {
        self::assertArrayNotHasKey('identityVerificationDocumentType', $this->createRequest()->toArray());
    }

    public function test_every_document_type_survives_the_payload_unchanged(): void
    {
        foreach (IdentityVerificationDocumentType::cases() as $case) {
            $array = $this->createRequest(identityVerificationDocumentType: $case)->toArray();

            self::assertSame($case->value, $array['identityVerificationDocumentType']);
        }
    }

    private function createRequest(
        ?string $externalOrderId = null,
        ?string $gksConfigurationId = null,
        ?string $contractPartnerKopaKey = null,
        ?IdentityVerificationDocumentType $identityVerificationDocumentType = null,
    ): VehicleRegistrationRequest {
        return new VehicleRegistrationRequest(
            email: 'test@example.com',
            customization: $this->createCustomization(),
            vehicleHolder: $this->createVehicleHolder(),
            externalOrderId: $externalOrderId,
            gksConfigurationId: $gksConfigurationId,
            contractPartnerKopaKey: $contractPartnerKopaKey,
            identityVerificationDocumentType: $identityVerificationDocumentType,
        );
    }

    private function createCustomization(
        VehicleRegistrationServiceTypeCode $code = VehicleRegistrationServiceTypeCode::NZ,
        ?string $iban = 'DE89370400440532013000',
        ?string $bic = 'COBADEFFXXX',
    ): VehicleRegistrationCustomization {
        $continuesRegistration = $code->requiresPreviousRegistration();

        return new VehicleRegistrationCustomization(
            licensePlateNumberAssignmentStrategy: new VehicleRegistrationLicensePlateNumberAssignmentStrategyRandom(
                licensePlateType: VehicleRegistrationLicensePlateType::Regular,
            ),
            vehicleRegistrationServiceTypeCode: $code,
            deregistered: true,
            vehicleType: VehicleRegistrationVehicleType::Car,
            electronicInsuranceConfirmationNumber: 'ABC1234',
            vehicleIdentificationNumber: 'WBA12345678901234',
            vehicleTitleSecurityCode: 'ABCDEF123456',
            iban: $iban,
            bic: $bic,
            vehicleRegistrationCertificateSecurityCode: $continuesRegistration ? 'SEC1234' : null,
            previousLicensePlate: $continuesRegistration
                ? new VehicleRegistrationPreviousLicensePlate(
                    licensePlateNumberComponents: new EuroLicensePlateNumberComponents('M', 'KG', '6988'),
                    licensePlateType: VehicleRegistrationLicensePlateType::Regular,
                )
                : null,
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

    public function test_the_request_takes_any_holder_variant(): void
    {
        $naturalPerson = new VehicleRegistrationRequest(
            email: 'test@example.com',
            customization: $this->createCustomization(iban: null, bic: null),
            vehicleHolder: $this->createNaturalPersonHolder(),
        );

        self::assertSame('NATURAL_PERSON', $naturalPerson->toArray()['vehicleHolder']['type']);

        $legalPerson = new VehicleRegistrationRequest(
            email: 'test@example.com',
            customization: $this->createCustomization(iban: null, bic: null),
            vehicleHolder: $this->createLegalPersonHolder(),
        );

        self::assertSame('LEGAL_PERSON', $legalPerson->toArray()['vehicleHolder']['type']);
        self::assertSame('Autohaus Muster GmbH', $legalPerson->toArray()['vehicleHolder']['personData']['name']);
    }

    /**
     * The payload of the spec example for a natural person, apart from the
     * values: identity document and bank details on the holder, none of them
     * on the request or in the customization.
     */
    public function test_a_natural_person_sends_identity_document_and_bank_details_on_the_holder(): void
    {
        $array = (new VehicleRegistrationRequest(
            email: 'test@example.com',
            customization: $this->createCustomization(iban: null, bic: null),
            vehicleHolder: $this->createNaturalPersonHolder(bic: 'COBADEFFXXX'),
        ))->toArray();

        self::assertArrayNotHasKey('identityVerificationDocumentType', $array);
        self::assertArrayNotHasKey('iban', $array['customization']);
        self::assertArrayNotHasKey('bic', $array['customization']);
        self::assertSame('GERMAN_ID', $array['vehicleHolder']['identityVerificationDocumentType']);
        self::assertSame('DE89370400440532013000', $array['vehicleHolder']['iban']);
        self::assertSame('COBADEFFXXX', $array['vehicleHolder']['bic']);
    }

    /**
     * 2.7.0 moved these fields onto the holder. Sending them in the old place
     * as well would be ignored at best, so a caller mixing the two shapes
     * learns it here rather than from a registration that debits nobody.
     */
    public function test_a_2_7_0_holder_rejects_the_fields_it_carries_itself(): void
    {
        $holders = [
            'NATURAL_PERSON' => $this->createNaturalPersonHolder(),
            'LEGAL_PERSON' => $this->createLegalPersonHolder(),
        ];

        foreach ($holders as $type => $holder) {
            foreach ([
                'customization.iban' => fn (): VehicleRegistrationRequest => new VehicleRegistrationRequest(
                    email: 'test@example.com',
                    customization: $this->createCustomization(bic: null),
                    vehicleHolder: $holder,
                ),
                'customization.bic' => fn (): VehicleRegistrationRequest => new VehicleRegistrationRequest(
                    email: 'test@example.com',
                    customization: $this->createCustomization(iban: null),
                    vehicleHolder: $holder,
                ),
                'identityVerificationDocumentType' => fn (): VehicleRegistrationRequest => new VehicleRegistrationRequest(
                    email: 'test@example.com',
                    customization: $this->createCustomization(iban: null, bic: null),
                    vehicleHolder: $holder,
                    identityVerificationDocumentType: IdentityVerificationDocumentType::GermanId,
                ),
            ] as $field => $build) {
                try {
                    $build();

                    self::fail(sprintf('Expected %s to be rejected for %s', $field, $type));
                } catch (DropshippingException $exception) {
                    self::assertSame(
                        sprintf('Field "%s" must be null for a vehicleHolder of type %s, which carries it itself', $field, $type),
                        $exception->getMessage(),
                    );
                }
            }
        }
    }

    public function test_a_natural_person_needs_an_iban_for_every_code_but_ha(): void
    {
        foreach (VehicleRegistrationServiceTypeCode::cases() as $code) {
            if ($code === VehicleRegistrationServiceTypeCode::HA) {
                continue;
            }

            try {
                new VehicleRegistrationRequest(
                    email: 'test@example.com',
                    customization: $this->createCustomization($code, iban: null, bic: null),
                    vehicleHolder: $this->createNaturalPersonHolder(iban: null),
                );

                self::fail(sprintf('Expected %s to require an iban', $code->value));
            } catch (DropshippingException $exception) {
                self::assertSame(
                    sprintf('Field "vehicleHolder.iban" is required for vehicleRegistrationServiceTypeCode %s', $code->value),
                    $exception->getMessage(),
                );
            }
        }
    }

    public function test_a_natural_person_can_order_a_holder_change_without_iban(): void
    {
        $request = new VehicleRegistrationRequest(
            email: 'test@example.com',
            customization: $this->createHolderChangeCustomization(),
            vehicleHolder: $this->createNaturalPersonHolder(iban: null),
        );

        self::assertSame('HA', $request->toArray()['customization']['vehicleRegistrationServiceTypeCode']);
        self::assertArrayNotHasKey('iban', $request->toArray()['vehicleHolder']);
        self::assertSame(VehicleRegistrationVehicleHolderType::NaturalPerson, $request->vehicleHolder->holderType());
    }

    public function test_a_legal_person_cannot_order_a_holder_change(): void
    {
        foreach ([$this->createLegalPersonHolder(), $this->createDurablePowerOfAttorneyHolder()] as $holder) {
            try {
                new VehicleRegistrationRequest(
                    email: 'test@example.com',
                    customization: $this->createHolderChangeCustomization(),
                    vehicleHolder: $holder,
                );

                self::fail('Expected HA to be rejected for a legal person');
            } catch (DropshippingException $exception) {
                self::assertSame(
                    'A vehicleHolder of type LEGAL_PERSON is not supported for vehicleRegistrationServiceTypeCode HA',
                    $exception->getMessage(),
                );
            }
        }
    }

    public function test_a_legal_person_can_order_every_other_code(): void
    {
        foreach (VehicleRegistrationServiceTypeCode::cases() as $code) {
            if ($code === VehicleRegistrationServiceTypeCode::HA) {
                continue;
            }

            foreach ([$this->createLegalPersonHolder(), $this->createDurablePowerOfAttorneyHolder()] as $holder) {
                $request = new VehicleRegistrationRequest(
                    email: 'test@example.com',
                    customization: $this->createCustomization($code, iban: null, bic: null),
                    vehicleHolder: $holder,
                );

                self::assertSame($code->value, $request->toArray()['customization']['vehicleRegistrationServiceTypeCode']);
            }
        }
    }

    public function test_a_customs_major_customer_id_replaces_the_bank_details(): void
    {
        $array = (new VehicleRegistrationRequest(
            email: 'test@example.com',
            customization: $this->createCustomization(iban: null, bic: null),
            vehicleHolder: $this->createLegalPersonHolder(customsMajorCustomerId: '123A4567'),
        ))->toArray();

        self::assertArrayNotHasKey('iban', $array['customization']);
        self::assertArrayNotHasKey('iban', $array['vehicleHolder']['personData']);
        self::assertSame('123A4567', $array['vehicleHolder']['personData']['customsMajorCustomerId']);
    }

    public function test_a_durable_power_of_attorney_is_sent_as_person_data(): void
    {
        $request = new VehicleRegistrationRequest(
            email: 'test@example.com',
            customization: $this->createCustomization(iban: null, bic: null),
            vehicleHolder: $this->createDurablePowerOfAttorneyHolder(),
        );

        self::assertSame([
            'type' => 'LEGAL_PERSON',
            'personData' => [
                'type' => 'DURABLE_POWER_OF_ATTORNEY_REFERENCE',
                'durablePowerOfAttorneyId' => '11111111-1111-1111-1111-111111111111',
            ],
        ], $request->toArray()['vehicleHolder']);
    }

    /**
     * The bank details are part of the power of attorney, so the request needs
     * none for any code a legal person may order.
     */
    public function test_a_durable_power_of_attorney_needs_no_bank_details(): void
    {
        foreach (VehicleRegistrationServiceTypeCode::cases() as $code) {
            if (!$code->supportsLegalPersonVehicleHolder()) {
                continue;
            }

            $array = (new VehicleRegistrationRequest(
                email: 'test@example.com',
                customization: $this->createCustomization($code, iban: null, bic: null),
                vehicleHolder: $this->createDurablePowerOfAttorneyHolder(),
            ))->toArray();

            self::assertArrayNotHasKey('iban', $array['customization']);
        }
    }

    /**
     * The spec forbids it for every legal person: a durable power of attorney
     * already went through identification and signature, and any other legal
     * person would usually have to sign again.
     */
    public function test_a_legal_person_cannot_reuse_an_earlier_order(): void
    {
        foreach ([$this->createLegalPersonHolder(), $this->createDurablePowerOfAttorneyHolder()] as $holder) {
            try {
                new VehicleRegistrationRequest(
                    email: 'einkauf@autohaus-muster.de',
                    customization: $this->createCustomization(iban: null, bic: null),
                    vehicleHolder: $holder,
                    recycledVehicleRegistrationOrderId: 999,
                );

                self::fail('Expected recycling to be rejected for a legal person');
            } catch (DropshippingException $exception) {
                self::assertSame(
                    'Field "recycledVehicleRegistrationOrderId" must be null for a vehicleHolder of type LEGAL_PERSON',
                    $exception->getMessage(),
                );
            }
        }
    }

    public function test_a_natural_person_can_still_reuse_an_earlier_order(): void
    {
        $array = (new VehicleRegistrationRequest(
            email: 'test@example.com',
            customization: $this->createCustomization(iban: null, bic: null),
            vehicleHolder: $this->createNaturalPersonHolder(),
            recycledVehicleRegistrationOrderId: 999,
        ))->toArray();

        self::assertSame(['orderId' => 999], $array['recycledVehicleRegistration']);
    }

    /**
     * The rule moved here from the customization in SDK 2.7.0, because whether
     * the IBAN is required depends on the holder.
     */
    public function test_the_pre_2_7_0_holder_needs_the_iban_in_the_customization_for_every_code_but_ha(): void
    {
        foreach (VehicleRegistrationServiceTypeCode::cases() as $code) {
            if ($code === VehicleRegistrationServiceTypeCode::HA) {
                continue;
            }

            try {
                new VehicleRegistrationRequest(
                    email: 'test@example.com',
                    customization: $this->createCustomization($code, iban: null, bic: null),
                    vehicleHolder: $this->createVehicleHolder(),
                );

                self::fail(sprintf('Expected %s to require an iban', $code->value));
            } catch (DropshippingException $exception) {
                self::assertSame(
                    sprintf('Field "customization.iban" is required for vehicleRegistrationServiceTypeCode %s', $code->value),
                    $exception->getMessage(),
                );
            }
        }
    }

    public function test_a_holder_change_needs_no_iban(): void
    {
        $array = (new VehicleRegistrationRequest(
            email: 'test@example.com',
            customization: $this->createHolderChangeCustomization(),
            vehicleHolder: $this->createVehicleHolder(),
        ))->toArray();

        self::assertArrayNotHasKey('iban', $array['customization']);
    }

    public function test_toArray_wraps_the_recycled_order_id_in_its_object(): void
    {
        $array = (new VehicleRegistrationRequest(
            email: 'test@example.com',
            customization: $this->createCustomization(),
            vehicleHolder: $this->createVehicleHolder(),
            recycledVehicleRegistrationOrderId: 12345,
        ))->toArray();

        self::assertSame(['orderId' => 12345], $array['recycledVehicleRegistration']);
    }

    public function test_toArray_omits_the_recycled_registration_when_not_set(): void
    {
        self::assertArrayNotHasKey('recycledVehicleRegistration', $this->createRequest()->toArray());
    }

    /**
     * The API assigns order IDs, so a zero or negative one cannot name an order.
     * Catching it here saves a round trip.
     */
    public function test_constructor_rejects_a_non_positive_recycled_order_id(): void
    {
        foreach ([0, -1, PHP_INT_MIN] as $orderId) {
            try {
                new VehicleRegistrationRequest(
                    email: 'test@example.com',
                    customization: $this->createCustomization(),
                    vehicleHolder: $this->createVehicleHolder(),
                    recycledVehicleRegistrationOrderId: $orderId,
                );

                self::fail(sprintf('Expected the order ID %d to be rejected', $orderId));
            } catch (DropshippingException $exception) {
                self::assertStringContainsString('recycledVehicleRegistrationOrderId', $exception->getMessage());
            }
        }
    }

    public function test_constructor_accepts_a_large_recycled_order_id(): void
    {
        // int64 on the API side, so the whole PHP int range has to pass.
        $request = new VehicleRegistrationRequest(
            email: 'test@example.com',
            customization: $this->createCustomization(),
            vehicleHolder: $this->createVehicleHolder(),
            recycledVehicleRegistrationOrderId: PHP_INT_MAX,
        );

        self::assertSame(['orderId' => PHP_INT_MAX], $request->toArray()['recycledVehicleRegistration']);
    }

    private function createNaturalPersonHolder(
        ?string $iban = 'DE89370400440532013000',
        ?string $bic = null,
    ): VehicleRegistrationNaturalPersonVehicleHolder {
        return new VehicleRegistrationNaturalPersonVehicleHolder(
            address: $this->createAddress(),
            placeOfBirth: 'Berlin',
            birthDate: '1990-01-31',
            identityVerificationDocumentType: IdentityVerificationDocumentType::GermanId,
            iban: $iban,
            bic: $bic,
        );
    }

    private function createDurablePowerOfAttorneyHolder(): VehicleRegistrationLegalPersonVehicleHolder
    {
        return new VehicleRegistrationLegalPersonVehicleHolder(
            personData: new VehicleRegistrationLegalPersonDurablePowerOfAttorneyReferencePersonData('11111111-1111-1111-1111-111111111111'),
        );
    }

    private function createLegalPersonHolder(?string $customsMajorCustomerId = null): VehicleRegistrationLegalPersonVehicleHolder
    {
        return new VehicleRegistrationLegalPersonVehicleHolder(
            personData: new VehicleRegistrationLegalPersonProvidedPersonData(
                address: $this->createAddress(),
                name: 'Autohaus Muster GmbH',
                industryCode: 'G01',
                companyNumber: 'HRB 123456',
                customsMajorCustomerId: $customsMajorCustomerId,
                iban: $customsMajorCustomerId === null ? 'DE89370400440532013000' : null,
            ),
        );
    }

    private function createHolderChangeCustomization(): VehicleRegistrationCustomization
    {
        return new VehicleRegistrationCustomization(
            licensePlateNumberAssignmentStrategy: new VehicleRegistrationLicensePlateNumberAssignmentStrategyRetained(),
            vehicleRegistrationServiceTypeCode: VehicleRegistrationServiceTypeCode::HA,
            deregistered: false,
            vehicleType: VehicleRegistrationVehicleType::Car,
            electronicInsuranceConfirmationNumber: null,
            vehicleIdentificationNumber: 'WBA12345678901234',
            vehicleTitleSecurityCode: null,
            iban: null,
            bic: null,
            vehicleRegistrationCertificateSecurityCode: 'SEC1234',
            previousLicensePlate: new VehicleRegistrationPreviousLicensePlate(
                licensePlateNumberComponents: new EuroLicensePlateNumberComponents('M', 'KG', '6988'),
                licensePlateType: VehicleRegistrationLicensePlateType::Regular,
            ),
        );
    }
}
