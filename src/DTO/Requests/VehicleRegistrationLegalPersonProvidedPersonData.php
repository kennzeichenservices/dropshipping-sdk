<?php

declare(strict_types=1);

namespace Dropshipping\DTO\Requests;

use Dropshipping\DTO\Address;
use Dropshipping\Enums\VehicleRegistrationLegalPersonPersonDataType;
use Dropshipping\Exceptions\DropshippingException;
use Dropshipping\Support\Iban;
use Dropshipping\Support\Validator;

/**
 * Person data of a legal person vehicle holder sent with the registration.
 * Requires API 2.7.0.
 *
 * ## What the address means here
 *
 * The address is the address *of the legal person*. Its `firstName`, `lastName`
 * and `gender` name a contact person, not the holder, and its `companyName` is
 * not transmitted as the holder's name: that is what {@see $name} is for. So a
 * company whose address carries a `companyName` still has to repeat it in
 * `$name`, and the two are allowed to differ.
 *
 * ## Industry code
 *
 * {@see $industryCode} has to be a code the API knows. Fetch the list from
 * {@see \Dropshipping\Endpoints\VehicleHolderIndustries\VehicleHolderIndustriesEndpoint::getAll()}
 * and pass the `code` of the entry that fits the company. The three-character
 * shape is checked here; whether the code exists is only known to the API.
 *
 * ## Vehicle tax
 *
 * The company settles the motor vehicle tax either by a SEPA direct debit
 * mandate, with {@see $iban} and, for an IBAN from Monaco, San Marino or
 * Switzerland, {@see $bic}, or through the customs administration with a
 * {@see $customsMajorCustomerId}. Exactly one of the two is required: the spec
 * forbids the IBAN and BIC next to a customs ID and requires the IBAN for every
 * service type code but `HA`, which a legal person cannot order anyway.
 *
 * Up to SDK 2.7.3 the IBAN and BIC were part of the customization.
 */
final readonly class VehicleRegistrationLegalPersonProvidedPersonData implements VehicleRegistrationLegalPersonPersonDataInterface
{
    /** Three characters, upper case letters and digits, e.g. "G01". */
    private const INDUSTRY_CODE_PATTERN = '/^[A-Z0-9]{3}$/D';

    /** Three digits, one upper case letter, four digits, e.g. "123A4567". */
    private const CUSTOMS_MAJOR_CUSTOMER_ID_PATTERN = '/^[0-9]{3}[A-Z][0-9]{4}$/D';

    /** At least one non-whitespace character. */
    private const NOT_BLANK_PATTERN = '/\S/';

    /**
     * @param Address     $address                Address of the legal person. Its name fields name a contact person, see the class docblock.
     * @param string      $name                   Name of the legal person, 1–255 characters.
     * @param string      $industryCode           Code of the industry (Wirtschaftszweig) of the legal person, exactly three characters of A–Z and 0–9. See GET /vehicleHolderIndustries.
     * @param string      $companyNumber          Company register number for the company verification, e.g. "HRB 123456", 1–100 characters and not blank.
     * @param string|null $customsMajorCustomerId Major customer ID of the customs administration (Zoll-GK-Nr.), three digits, an upper case letter and four digits. Replaces the SEPA direct debit mandate, so $iban and $bic must be null with it.
     * @param string|null $iban                   IBAN for the recurring vehicle tax debit, 15–34 characters. Required unless $customsMajorCustomerId is set.
     * @param string|null $bic                    BIC belonging to the IBAN, 8–11 characters. Required for an IBAN starting with MC, SM or CH, optional otherwise.
     *
     * @throws DropshippingException When a value violates the API constraints.
     */
    public function __construct(
        public Address $address,
        public string $name,
        public string $industryCode,
        public string $companyNumber,
        public ?string $customsMajorCustomerId = null,
        public ?string $iban = null,
        public ?string $bic = null,
    ) {
        Validator::requireStringLength($name, 'personData.name', 1, 255);
        Validator::requirePattern($industryCode, 'personData.industryCode', self::INDUSTRY_CODE_PATTERN, 'be three characters of A-Z and 0-9');
        Validator::requireStringLength($companyNumber, 'personData.companyNumber', 1, 100);
        Validator::requirePattern($companyNumber, 'personData.companyNumber', self::NOT_BLANK_PATTERN, 'not be blank');
        Validator::requireNullablePattern(
            $customsMajorCustomerId,
            'personData.customsMajorCustomerId',
            self::CUSTOMS_MAJOR_CUSTOMER_ID_PATTERN,
            'be three digits, an upper case letter and four digits',
        );
        Validator::requireNullableStringLength($iban, 'personData.iban', 15, 34);
        Validator::requireNullableStringLength($bic, 'personData.bic', 8, 11);

        if ($customsMajorCustomerId !== null) {
            $whenCustoms = 'when personData.customsMajorCustomerId is set';

            Validator::requireNull($iban, 'personData.iban', $whenCustoms);
            Validator::requireNull($bic, 'personData.bic', $whenCustoms);
        } else {
            Validator::requireNotNull($iban, 'personData.iban', 'unless personData.customsMajorCustomerId is set');
        }

        if ($iban !== null && Iban::requiresBic($iban)) {
            Validator::requireNotNull($bic, 'personData.bic', sprintf('for an iban starting with %s', mb_substr($iban, 0, 2)));
        }
    }

    public function type(): VehicleRegistrationLegalPersonPersonDataType
    {
        return VehicleRegistrationLegalPersonPersonDataType::Provided;
    }

    public function address(): Address
    {
        return $this->address;
    }

    public function customsMajorCustomerId(): ?string
    {
        return $this->customsMajorCustomerId;
    }

    /**
     * Convert the person data to an associative array, omitting the optional
     * fields when null.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'type' => $this->type()->value,
            'name' => $this->name,
            'companyNumber' => $this->companyNumber,
            'industryCode' => $this->industryCode,
            'customsMajorCustomerId' => $this->customsMajorCustomerId,
            'iban' => $this->iban,
            'bic' => $this->bic,
            'address' => $this->address->toArray(),
        ], static fn (mixed $value): bool => $value !== null);
    }
}
