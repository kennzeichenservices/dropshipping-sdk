<?php

declare(strict_types=1);

namespace Dropshipping\Tests\Unit\DTO\Requests;

use Dropshipping\DS;
use Dropshipping\DTO\Requests\VehicleRegistrationDurablePowerOfAttorneyCompany;
use Dropshipping\DTO\Requests\VehicleRegistrationDurablePowerOfAttorneyCreationRequest;
use Dropshipping\DTO\Requests\VehicleRegistrationDurablePowerOfAttorneyCustomsMajorCustomerTaxData;
use Dropshipping\DTO\Requests\VehicleRegistrationDurablePowerOfAttorneySepaDirectDebitMandateTaxData;
use Dropshipping\DTO\Requests\VehicleRegistrationDurablePowerOfAttorneySigner;
use Dropshipping\Exceptions\DropshippingException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class VehicleRegistrationDurablePowerOfAttorneyCreationRequestTest extends TestCase
{
    public function test_minimal_request_leaves_out_every_optional_field(): void
    {
        $request = new VehicleRegistrationDurablePowerOfAttorneyCreationRequest(
            company: self::company(new VehicleRegistrationDurablePowerOfAttorneySepaDirectDebitMandateTaxData('DE89370400440532013000')),
            signer: self::signer(),
        );

        self::assertSame([
            'company' => [
                'name' => 'Muster GmbH',
                'streetName' => 'Hauptstr.',
                'houseNumber' => '1',
                'zipCode' => '10115',
                'cityName' => 'Berlin',
                'taxData' => ['type' => 'SEPA_DIRECT_DEBIT_MANDATE', 'iban' => 'DE89370400440532013000'],
            ],
            'signer' => ['firstName' => 'Erika', 'lastName' => 'Musterfrau', 'email' => 'erika@example.com'],
        ], $request->toArray());
    }

    public function test_customs_tax_data_sends_only_type_and_id(): void
    {
        $company = self::company(new VehicleRegistrationDurablePowerOfAttorneyCustomsMajorCustomerTaxData('123A4567'));

        self::assertSame(
            ['type' => 'CUSTOMS_MAJOR_CUSTOMER', 'customsMajorCustomerId' => '123A4567'],
            $company->toArray()['taxData'],
        );
    }

    public function test_ds_builds_the_same_request_as_the_constructors(): void
    {
        $viaDs = DS::durablePowerOfAttorney(
            company: DS::durablePowerOfAttorneyCompany(
                name: 'Muster GmbH',
                streetName: 'Hauptstr.',
                houseNumber: '1',
                zipCode: '10115',
                cityName: 'Berlin',
                taxData: DS::durablePowerOfAttorneyCustomsTaxData('123A4567'),
                number: 'HRB 1',
            ),
            signerFirstName: 'Erika',
            signerLastName: 'Musterfrau',
            signerEmail: 'erika@example.com',
            expirationDate: '2027-01-31',
        );

        $direct = new VehicleRegistrationDurablePowerOfAttorneyCreationRequest(
            company: self::company(new VehicleRegistrationDurablePowerOfAttorneyCustomsMajorCustomerTaxData('123A4567'), number: 'HRB 1'),
            signer: self::signer(),
            expirationDate: '2027-01-31',
        );

        self::assertSame($direct->toArray(), $viaDs->toArray());
        self::assertSame('2027-01-31', $viaDs->toArray()['expirationDate']);
        self::assertSame('HRB 1', $viaDs->toArray()['company']['number']);
    }

    #[DataProvider('validExpirationDates')]
    public function test_accepts_a_valid_expiration_date(string $date): void
    {
        $request = new VehicleRegistrationDurablePowerOfAttorneyCreationRequest(
            company: self::company(new VehicleRegistrationDurablePowerOfAttorneyCustomsMajorCustomerTaxData('123A4567')),
            signer: self::signer(),
            expirationDate: $date,
        );

        self::assertSame($date, $request->expirationDate);
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function validExpirationDates(): iterable
    {
        yield 'regular' => ['2027-10-31'];
        yield 'leap day' => ['2028-02-29'];
    }

    #[DataProvider('invalidExpirationDates')]
    public function test_rejects_an_invalid_expiration_date(string $date): void
    {
        $this->expectException(DropshippingException::class);
        $this->expectExceptionMessage('"expirationDate" must be a date in the form YYYY-MM-DD');

        new VehicleRegistrationDurablePowerOfAttorneyCreationRequest(
            company: self::company(new VehicleRegistrationDurablePowerOfAttorneyCustomsMajorCustomerTaxData('123A4567')),
            signer: self::signer(),
            expirationDate: $date,
        );
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function invalidExpirationDates(): iterable
    {
        yield 'empty' => [''];
        yield 'german format' => ['31.10.2027'];
        yield 'no leading zeros' => ['2027-1-1'];
        yield 'day that does not exist' => ['2027-02-30'];
        yield 'no leap year' => ['2027-02-29'];
        yield 'month 13' => ['2027-13-01'];
        yield 'with time' => ['2027-10-31T00:00:00'];
        yield 'trailing newline' => ["2027-10-31\n"];
    }

    #[DataProvider('invalidCompanies')]
    public function test_company_rejects_values_outside_the_spec(callable $build, string $field): void
    {
        $this->expectException(DropshippingException::class);
        $this->expectExceptionMessage(sprintf('"%s"', $field));

        $build();
    }

    /**
     * @return iterable<string, array{callable, string}>
     */
    public static function invalidCompanies(): iterable
    {
        $tax = new VehicleRegistrationDurablePowerOfAttorneyCustomsMajorCustomerTaxData('123A4567');

        yield 'empty name' => [static fn () => self::company($tax, name: ''), 'company.name'];
        yield 'blank name' => [static fn () => self::company($tax, name: '   '), 'company.name'];
        yield 'name over 100' => [static fn () => self::company($tax, name: str_repeat('a', 101)), 'company.name'];
        yield 'house number over 10' => [static fn () => self::company($tax, houseNumber: str_repeat('1', 11)), 'company.houseNumber'];
        yield 'zip over 12' => [static fn () => self::company($tax, zipCode: str_repeat('1', 13)), 'company.zipCode'];
        yield 'empty city' => [static fn () => self::company($tax, cityName: ''), 'company.cityName'];
        yield 'empty street' => [static fn () => self::company($tax, streetName: ''), 'company.streetName'];
        yield 'empty number' => [static fn () => self::company($tax, number: ''), 'company.number'];
        yield 'blank number' => [static fn () => self::company($tax, number: ' '), 'company.number'];
        yield 'number over 100' => [static fn () => self::company($tax, number: str_repeat('1', 101)), 'company.number'];
    }

    public function test_name_length_counts_characters_not_bytes(): void
    {
        $company = self::company(
            new VehicleRegistrationDurablePowerOfAttorneyCustomsMajorCustomerTaxData('123A4567'),
            name: str_repeat('ü', 100),
        );

        self::assertSame(100, mb_strlen($company->name));
    }

    #[DataProvider('invalidSigners')]
    public function test_signer_rejects_values_outside_the_spec(string $firstName, string $lastName, string $email, string $field): void
    {
        $this->expectException(DropshippingException::class);
        $this->expectExceptionMessage(sprintf('"%s"', $field));

        new VehicleRegistrationDurablePowerOfAttorneySigner($firstName, $lastName, $email);
    }

    /**
     * @return iterable<string, array{string, string, string, string}>
     */
    public static function invalidSigners(): iterable
    {
        yield 'empty first name' => ['', 'Musterfrau', 'erika@example.com', 'signer.firstName'];
        yield 'last name over 100' => ['Erika', str_repeat('a', 101), 'erika@example.com', 'signer.lastName'];
        yield 'no email' => ['Erika', 'Musterfrau', 'erika', 'signer.email'];
        yield 'email with header injection' => ['Erika', 'Musterfrau', "erika@example.com\r\nBcc: x@example.com", 'signer.email'];
        yield 'email too short' => ['Erika', 'Musterfrau', 'ab', 'signer.email'];
        yield 'email over 255' => ['Erika', 'Musterfrau', str_repeat('a', 251) . '@x.de', 'signer.email'];
    }

    #[DataProvider('invalidSepaTaxData')]
    public function test_sepa_tax_data_rejects_values_outside_the_spec(string $iban, ?string $bic, string $field): void
    {
        $this->expectException(DropshippingException::class);
        $this->expectExceptionMessage(sprintf('"%s"', $field));

        new VehicleRegistrationDurablePowerOfAttorneySepaDirectDebitMandateTaxData($iban, $bic);
    }

    /**
     * @return iterable<string, array{string, string|null, string}>
     */
    public static function invalidSepaTaxData(): iterable
    {
        yield 'iban too short' => ['DE89370400440', null, 'taxData.iban'];
        yield 'iban too long' => [str_repeat('1', 35), null, 'taxData.iban'];
        yield 'bic too short' => ['DE89370400440532013000', 'ABC', 'taxData.bic'];
        yield 'bic too long' => ['DE89370400440532013000', 'ABCDEFGHIJKL', 'taxData.bic'];
        yield 'swiss iban without bic' => ['CH9300762011623852957', null, 'taxData.bic'];
        yield 'monaco iban without bic' => ['MC5811222000010123456789030', null, 'taxData.bic'];
        yield 'san marino iban without bic' => ['SM86U0322509800000000270100', null, 'taxData.bic'];
        yield 'lower case swiss iban without bic' => ['ch9300762011623852957', null, 'taxData.bic'];
    }

    public function test_sepa_tax_data_accepts_a_swiss_iban_with_bic(): void
    {
        $taxData = new VehicleRegistrationDurablePowerOfAttorneySepaDirectDebitMandateTaxData('CH9300762011623852957', 'UBSWCHZH80A');

        self::assertSame('UBSWCHZH80A', $taxData->toArray()['bic']);
    }

    #[DataProvider('invalidCustomsIds')]
    public function test_customs_tax_data_rejects_a_malformed_id(string $id): void
    {
        $this->expectException(DropshippingException::class);
        $this->expectExceptionMessage('"taxData.customsMajorCustomerId"');

        new VehicleRegistrationDurablePowerOfAttorneyCustomsMajorCustomerTaxData($id);
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function invalidCustomsIds(): iterable
    {
        yield 'empty' => [''];
        yield 'lower case letter' => ['123a4567'];
        yield 'too long' => ['123A45678'];
        yield 'trailing newline' => ["123A4567\n"];
        yield 'with spaces' => ['123 A4567'];
    }

    private static function company(
        VehicleRegistrationDurablePowerOfAttorneyCustomsMajorCustomerTaxData|VehicleRegistrationDurablePowerOfAttorneySepaDirectDebitMandateTaxData $taxData,
        string $name = 'Muster GmbH',
        string $streetName = 'Hauptstr.',
        string $houseNumber = '1',
        string $zipCode = '10115',
        string $cityName = 'Berlin',
        ?string $number = null,
    ): VehicleRegistrationDurablePowerOfAttorneyCompany {
        return new VehicleRegistrationDurablePowerOfAttorneyCompany($name, $streetName, $houseNumber, $zipCode, $cityName, $taxData, $number);
    }

    private static function signer(): VehicleRegistrationDurablePowerOfAttorneySigner
    {
        return new VehicleRegistrationDurablePowerOfAttorneySigner('Erika', 'Musterfrau', 'erika@example.com');
    }
}
