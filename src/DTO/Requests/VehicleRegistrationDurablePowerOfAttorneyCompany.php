<?php

declare(strict_types=1);

namespace Dropshipping\DTO\Requests;

use Dropshipping\Exceptions\DropshippingException;
use Dropshipping\Support\Validator;

/**
 * The company a durable power of attorney is issued for.
 *
 * Unlike the address of a registration there is no country code: the spec
 * leaves it out, so the address is taken to be German.
 *
 * {@see $industryCode} has to be a code the API knows. Fetch the list from
 * {@see \Dropshipping\Endpoints\VehicleHolderIndustries\VehicleHolderIndustriesEndpoint::getAll()}
 * and pass the `code` of the entry that fits the company. The three-character
 * shape is checked here; whether the code exists is only known to the API.
 */
final readonly class VehicleRegistrationDurablePowerOfAttorneyCompany
{
    /** Three characters, upper case letters and digits, e.g. "G01". */
    private const INDUSTRY_CODE_PATTERN = '/^[A-Z0-9]{3}$/D';

    /** At least one non-whitespace character. */
    private const NOT_BLANK_PATTERN = '/\S/';

    /**
     * @param string                                                           $name         Company name, 1–100 characters.
     * @param string                                                           $industryCode Code of the industry (Wirtschaftszweig) of the company, exactly three characters of A–Z and 0–9. See GET /vehicleHolderIndustries.
     * @param string                                                           $streetName   Street, 1–100 characters.
     * @param string                                                           $houseNumber  House number, 1–10 characters.
     * @param string                                                           $zipCode      Postal code, 1–12 characters.
     * @param string                                                           $cityName     City, 1–100 characters.
     * @param VehicleRegistrationDurablePowerOfAttorneyCompanyTaxDataInterface $taxData      How the company settles the motor vehicle tax.
     * @param string|null                                                      $number       Company register number, e.g. "HRB 123456", 1–100 characters. Optional in the spec.
     *
     * @throws DropshippingException When a value violates the API constraints.
     */
    public function __construct(
        public string $name,
        public string $industryCode,
        public string $streetName,
        public string $houseNumber,
        public string $zipCode,
        public string $cityName,
        public VehicleRegistrationDurablePowerOfAttorneyCompanyTaxDataInterface $taxData,
        public ?string $number = null,
    ) {
        Validator::requireStringLength($name, 'company.name', 1, 100);
        Validator::requirePattern($name, 'company.name', self::NOT_BLANK_PATTERN, 'not be blank');
        Validator::requirePattern($industryCode, 'company.industryCode', self::INDUSTRY_CODE_PATTERN, 'be three characters of A-Z and 0-9');
        Validator::requireStringLength($streetName, 'company.streetName', 1, 100);
        Validator::requireStringLength($houseNumber, 'company.houseNumber', 1, 10);
        Validator::requireStringLength($zipCode, 'company.zipCode', 1, 12);
        Validator::requireStringLength($cityName, 'company.cityName', 1, 100);
        Validator::requireNullableStringLength($number, 'company.number', 1, 100);
        Validator::requireNullablePattern($number, 'company.number', self::NOT_BLANK_PATTERN, 'not be blank');
    }

    /**
     * Convert the company to an associative array, omitting the number when null.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $data = [
            'name' => $this->name,
            'industryCode' => $this->industryCode,
            'streetName' => $this->streetName,
            'houseNumber' => $this->houseNumber,
            'zipCode' => $this->zipCode,
            'cityName' => $this->cityName,
            'taxData' => $this->taxData->toArray(),
        ];

        if ($this->number !== null) {
            $data['number'] = $this->number;
        }

        return $data;
    }
}
