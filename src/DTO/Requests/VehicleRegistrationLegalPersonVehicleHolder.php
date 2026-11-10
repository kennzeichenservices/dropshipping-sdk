<?php

declare(strict_types=1);

namespace Dropshipping\DTO\Requests;

use Dropshipping\DTO\Address;
use Dropshipping\Enums\VehicleRegistrationVehicleHolderType;
use Dropshipping\Exceptions\DropshippingException;
use Dropshipping\Support\Validator;

/**
 * Request DTO representing a legal person (juristische Person) as the vehicle
 * holder. Requires API 2.7.0, which introduced the variant.
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
 * A legal person can settle the motor vehicle tax through the customs
 * administration instead of a SEPA direct debit mandate, by carrying a
 * {@see $customsMajorCustomerId}. Where it is set, the customization must leave
 * `iban` and `bic` empty; {@see VehicleRegistrationRequest} enforces that pair,
 * because only there are the holder and the customization both known.
 *
 * ## Not supported for HA
 *
 * A Halteränderung (`HA`) cannot be ordered for a legal person. The combination
 * is rejected by {@see VehicleRegistrationRequest}.
 */
final readonly class VehicleRegistrationLegalPersonVehicleHolder implements VehicleRegistrationVehicleHolderInterface
{
    /** Three characters, upper case letters and digits, e.g. "G01". */
    private const INDUSTRY_CODE_PATTERN = '/^[A-Z0-9]{3}$/';

    /** Three digits, one upper case letter, four digits, e.g. "123A4567". */
    private const CUSTOMS_MAJOR_CUSTOMER_ID_PATTERN = '/^[0-9]{3}[A-Z][0-9]{4}$/';

    /** At least one non-whitespace character. */
    private const NOT_BLANK_PATTERN = '/\S/';

    /**
     * @param Address     $address                 Address of the legal person. Its name fields name a contact person, see the class docblock.
     * @param string      $name                    Name of the legal person, 1–255 characters.
     * @param string      $industryCode            Code of the industry (Wirtschaftszweig) of the legal person, exactly three characters of A–Z and 0–9. See GET /vehicleHolderIndustries.
     * @param string      $companyNumber           Company register number for the company verification, e.g. "HRB 123456", 1–100 characters and not blank.
     * @param string|null $customsMajorCustomerId  Optional major customer ID of the customs administration (Zoll-GK-Nr.), three digits, an upper case letter and four digits. Replaces the SEPA direct debit mandate for the motor vehicle tax.
     *
     * @throws DropshippingException When a value violates the API constraints.
     */
    public function __construct(
        public Address $address,
        public string $name,
        public string $industryCode,
        public string $companyNumber,
        public ?string $customsMajorCustomerId = null,
    ) {
        Validator::requireStringLength($name, 'name', 1, 255);
        Validator::requirePattern($industryCode, 'industryCode', self::INDUSTRY_CODE_PATTERN, 'be three characters of A-Z and 0-9');
        Validator::requireStringLength($companyNumber, 'companyNumber', 1, 100);
        Validator::requirePattern($companyNumber, 'companyNumber', self::NOT_BLANK_PATTERN, 'not be blank');
        Validator::requireNullablePattern(
            $customsMajorCustomerId,
            'customsMajorCustomerId',
            self::CUSTOMS_MAJOR_CUSTOMER_ID_PATTERN,
            'be three digits, an upper case letter and four digits',
        );
    }

    /**
     * Narrowed to a non-nullable return: only the pre-2.7.0 holder sends no
     * discriminator.
     */
    public function holderType(): VehicleRegistrationVehicleHolderType
    {
        return VehicleRegistrationVehicleHolderType::LegalPerson;
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
     * Convert the vehicle holder data to an associative array, omitting the
     * customs major customer ID when null.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $data = [
            'type' => VehicleRegistrationVehicleHolderType::LegalPerson->value,
            'address' => $this->address->toArray(),
            'name' => $this->name,
            'industryCode' => $this->industryCode,
            'companyNumber' => $this->companyNumber,
        ];

        if ($this->customsMajorCustomerId !== null) {
            $data['customsMajorCustomerId'] = $this->customsMajorCustomerId;
        }

        return $data;
    }
}
