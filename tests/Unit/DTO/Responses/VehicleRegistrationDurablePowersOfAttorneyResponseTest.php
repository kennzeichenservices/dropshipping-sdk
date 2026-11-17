<?php

declare(strict_types=1);

namespace Dropshipping\Tests\Unit\DTO\Responses;

use Dropshipping\DTO\Responses\VehicleRegistrationDurablePowerOfAttorney;
use Dropshipping\DTO\Responses\VehicleRegistrationDurablePowersOfAttorneyResponse;
use Dropshipping\Enums\VehicleRegistrationDurablePowerOfAttorneyCompanyTaxDataType;
use Dropshipping\Enums\VehicleRegistrationDurablePowerOfAttorneyStatus;
use Dropshipping\Exceptions\DropshippingException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class VehicleRegistrationDurablePowersOfAttorneyResponseTest extends TestCase
{
    public function test_fromArray_reads_the_spec_example(): void
    {
        $response = VehicleRegistrationDurablePowersOfAttorneyResponse::fromArray([
            'durablePowersOfAttorney' => [self::record()],
        ]);

        $record = $response->durablePowersOfAttorney[0];

        self::assertSame('11111111-1111-1111-1111-111111111111', $record->id);
        self::assertSame('2000-10-31', $record->expirationDate);
        self::assertTrue($record->active);
        self::assertSame(VehicleRegistrationDurablePowerOfAttorneyStatus::Incomplete, $record->status);
        self::assertSame('Company X', $record->company->name);
        self::assertSame('HRB 123456', $record->company->number);
        self::assertSame('12A', $record->company->houseNumber);
        self::assertSame(VehicleRegistrationDurablePowerOfAttorneyCompanyTaxDataType::SepaDirectDebitMandate, $record->company->taxData->type);
        self::assertSame('DE0123456789000', $record->company->taxData->iban);
        self::assertSame('BYLADEM1001', $record->company->taxData->bic);
        self::assertNull($record->company->taxData->customsMajorCustomerId);
        self::assertSame('signer@localhost.test', $record->signer->email);
    }

    public function test_fromArray_reads_customs_tax_data_and_a_missing_company_number(): void
    {
        $data = self::record();
        unset($data['company']['number']);
        $data['company']['taxData'] = ['type' => 'CUSTOMS_MAJOR_CUSTOMER', 'customsMajorCustomerId' => '123A4567'];

        $record = VehicleRegistrationDurablePowerOfAttorney::fromArray($data);

        self::assertNull($record->company->number);
        self::assertSame(VehicleRegistrationDurablePowerOfAttorneyCompanyTaxDataType::CustomsMajorCustomer, $record->company->taxData->type);
        self::assertSame('123A4567', $record->company->taxData->customsMajorCustomerId);
        self::assertNull($record->company->taxData->iban);
    }

    /**
     * The spec declares customsMajorCustomerId nullable, so a null has to load.
     */
    public function test_fromArray_accepts_a_null_customs_id(): void
    {
        $data = self::record();
        $data['company']['taxData'] = ['type' => 'CUSTOMS_MAJOR_CUSTOMER', 'customsMajorCustomerId' => null];

        self::assertNull(VehicleRegistrationDurablePowerOfAttorney::fromArray($data)->company->taxData->customsMajorCustomerId);
    }

    /**
     * One record with a value added later must not take the whole listing down.
     */
    public function test_unknown_status_and_tax_data_type_degrade_to_unknown(): void
    {
        $data = self::record();
        $data['status'] = 'REVOKED';
        $data['company']['taxData'] = ['type' => 'SOMETHING_NEW'];

        $record = VehicleRegistrationDurablePowerOfAttorney::fromArray($data);

        self::assertSame(VehicleRegistrationDurablePowerOfAttorneyStatus::Unknown, $record->status);
        self::assertSame(VehicleRegistrationDurablePowerOfAttorneyCompanyTaxDataType::Unknown, $record->company->taxData->type);
        self::assertFalse($record->isUsable(new \DateTimeImmutable('1999-01-01')));
    }

    /**
     * Same as the XKFZ status: with a fallback in place, a missing or retyped
     * value is just another value this SDK cannot name.
     */
    public function test_missing_or_retyped_status_and_tax_type_degrade_to_unknown(): void
    {
        $data = self::record();
        unset($data['status'], $data['company']['taxData']['type']);

        $record = VehicleRegistrationDurablePowerOfAttorney::fromArray($data);

        self::assertSame(VehicleRegistrationDurablePowerOfAttorneyStatus::Unknown, $record->status);
        self::assertSame(VehicleRegistrationDurablePowerOfAttorneyCompanyTaxDataType::Unknown, $record->company->taxData->type);
        self::assertSame(
            VehicleRegistrationDurablePowerOfAttorneyStatus::Unknown,
            VehicleRegistrationDurablePowerOfAttorney::fromArray(['status' => 1] + self::record())->status,
        );
    }

    /**
     * Response validation stays out of the read path: data the request DTOs
     * would reject (a 16 character BIC, an over-long name) still loads.
     */
    public function test_fromArray_does_not_apply_request_constraints(): void
    {
        $data = self::record();
        $data['company']['name'] = str_repeat('x', 300);
        $data['company']['taxData']['bic'] = str_repeat('X', 16);

        $record = VehicleRegistrationDurablePowerOfAttorney::fromArray($data);

        self::assertSame(300, strlen($record->company->name));
    }

    /**
     * @param array<string, mixed> $data
     */
    #[DataProvider('malformedRecords')]
    public function test_fromArray_reports_a_malformed_record_as_sdk_exception(array $data, string $field): void
    {
        $this->expectException(DropshippingException::class);
        $this->expectExceptionMessage(sprintf('"%s"', $field));

        VehicleRegistrationDurablePowerOfAttorney::fromArray($data);
    }

    /**
     * @return iterable<string, array{array<string, mixed>, string}>
     */
    public static function malformedRecords(): iterable
    {
        $without = static function (string ...$path): array {
            $data = self::record();
            $ref = &$data;
            $last = array_pop($path);
            foreach ($path as $key) {
                $ref = &$ref[$key];
            }
            unset($ref[$last]);

            return $data;
        };

        yield 'missing id' => [$without('id'), 'id'];
        yield 'missing active' => [$without('active'), 'active'];
        yield 'missing expirationDate' => [$without('expirationDate'), 'expirationDate'];
        yield 'missing company' => [$without('company'), 'company'];
        yield 'missing signer' => [$without('signer'), 'signer'];
        yield 'missing taxData' => [$without('company', 'taxData'), 'taxData'];
        yield 'missing signer email' => [$without('signer', 'email'), 'email'];
        yield 'active as string' => [['active' => 'true'] + self::record(), 'active'];
        yield 'id as int' => [['id' => 1] + self::record(), 'id'];
        yield 'company as string' => [['company' => 'Company X'] + self::record(), 'company'];
    }

    public function test_listing_reports_a_missing_or_malformed_list(): void
    {
        foreach ([[], ['durablePowersOfAttorney' => 'x'], ['durablePowersOfAttorney' => ['x']]] as $data) {
            try {
                VehicleRegistrationDurablePowersOfAttorneyResponse::fromArray($data);
                self::fail('Expected DropshippingException');
            } catch (DropshippingException $e) {
                self::assertStringContainsString('durablePowersOfAttorney', $e->getMessage());
            }
        }
    }

    public function test_listing_turns_a_keyed_object_into_a_list(): void
    {
        $response = VehicleRegistrationDurablePowersOfAttorneyResponse::fromArray([
            'durablePowersOfAttorney' => ['a' => self::record(), 'b' => self::record()],
        ]);

        self::assertTrue(array_is_list($response->durablePowersOfAttorney));
        self::assertCount(2, $response->durablePowersOfAttorney);
    }

    public function test_empty_listing(): void
    {
        $response = VehicleRegistrationDurablePowersOfAttorneyResponse::fromArray(['durablePowersOfAttorney' => []]);

        self::assertSame([], $response->durablePowersOfAttorney);
        self::assertSame([], $response->usable());
        self::assertNull($response->findById('11111111-1111-1111-1111-111111111111'));
    }

    public function test_findById_ignores_letter_case(): void
    {
        $response = VehicleRegistrationDurablePowersOfAttorneyResponse::fromArray([
            'durablePowersOfAttorney' => [['id' => 'abcdef01-2345-6789-abcd-ef0123456789'] + self::record()],
        ]);

        self::assertNotNull($response->findById('ABCDEF01-2345-6789-ABCD-EF0123456789'));
        self::assertNull($response->findById('abcdef01-2345-6789-abcd-ef012345678'));
    }

    #[DataProvider('usability')]
    public function test_isUsable(bool $active, string $status, string $expirationDate, string $today, bool $expected): void
    {
        $record = VehicleRegistrationDurablePowerOfAttorney::fromArray([
            'active' => $active,
            'status' => $status,
            'expirationDate' => $expirationDate,
        ] + self::record());

        self::assertSame($expected, $record->isUsable(new \DateTimeImmutable($today)));
    }

    /**
     * @return iterable<string, array{bool, string, string, string, bool}>
     */
    public static function usability(): iterable
    {
        yield 'complete and valid' => [true, 'COMPLETE', '2027-10-31', '2027-01-01', true];
        yield 'last day counts' => [true, 'COMPLETE', '2027-10-31', '2027-10-31 23:59', true];
        yield 'expired' => [true, 'COMPLETE', '2027-10-31', '2027-11-01', false];
        yield 'revoked' => [false, 'COMPLETE', '2027-10-31', '2027-01-01', false];
        yield 'not signed yet' => [true, 'INCOMPLETE', '2027-10-31', '2027-01-01', false];
        yield 'failed' => [true, 'ERROR', '2027-10-31', '2027-01-01', false];
        yield 'timestamp instead of date' => [true, 'COMPLETE', '2027-10-31T00:00:00', '2027-10-31', true];
        yield 'unreadable date' => [true, 'COMPLETE', '31.10.2027', '2027-01-01', false];
        yield 'empty date' => [true, 'COMPLETE', '', '2027-01-01', false];
    }

    public function test_usable_filters_and_keeps_a_list(): void
    {
        $response = VehicleRegistrationDurablePowersOfAttorneyResponse::fromArray([
            'durablePowersOfAttorney' => [
                ['id' => 'a', 'status' => 'ERROR'] + self::record(),
                ['id' => 'b', 'status' => 'COMPLETE', 'expirationDate' => '2027-10-31'] + self::record(),
                ['id' => 'c', 'status' => 'COMPLETE', 'active' => false, 'expirationDate' => '2027-10-31'] + self::record(),
            ],
        ]);

        $usable = $response->usable(new \DateTimeImmutable('2027-01-01'));

        self::assertSame(['b'], array_map(static fn (VehicleRegistrationDurablePowerOfAttorney $p): string => $p->id, $usable));
    }

    /**
     * @return array<string, mixed>
     */
    private static function record(): array
    {
        return [
            'active' => true,
            'company' => [
                'cityName' => 'City X',
                'houseNumber' => '12A',
                'name' => 'Company X',
                'number' => 'HRB 123456',
                'streetName' => 'Street X',
                'zipCode' => '12345',
                'taxData' => ['type' => 'SEPA_DIRECT_DEBIT_MANDATE', 'iban' => 'DE0123456789000', 'bic' => 'BYLADEM1001'],
            ],
            'expirationDate' => '2000-10-31',
            'id' => '11111111-1111-1111-1111-111111111111',
            'signer' => ['email' => 'signer@localhost.test', 'firstName' => 'Company Name', 'lastName' => 'Company Number'],
            'status' => 'INCOMPLETE',
        ];
    }
}
