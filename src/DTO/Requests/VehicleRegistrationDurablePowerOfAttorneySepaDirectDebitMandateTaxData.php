<?php

declare(strict_types=1);

namespace Dropshipping\DTO\Requests;

use Dropshipping\Enums\VehicleRegistrationDurablePowerOfAttorneyCompanyTaxDataType;
use Dropshipping\Exceptions\DropshippingException;
use Dropshipping\Support\Iban;
use Dropshipping\Support\Validator;

/**
 * Tax data of a durable power of attorney settling the motor vehicle tax by a
 * SEPA direct debit mandate.
 *
 * The BIC is optional, except for an IBAN from Monaco, San Marino or
 * Switzerland, the same rule the registration customization applies.
 */
final readonly class VehicleRegistrationDurablePowerOfAttorneySepaDirectDebitMandateTaxData implements VehicleRegistrationDurablePowerOfAttorneyCompanyTaxDataInterface
{
    /**
     * @param string      $iban IBAN for the recurring vehicle tax debit, 15–34 characters.
     * @param string|null $bic  BIC belonging to the IBAN, 8–11 characters. Required for an IBAN starting with MC, SM or CH, optional otherwise.
     *
     * @throws DropshippingException When a value violates the API constraints.
     */
    public function __construct(
        public string $iban,
        public ?string $bic = null,
    ) {
        Validator::requireStringLength($iban, 'taxData.iban', 15, 34);
        Validator::requireNullableStringLength($bic, 'taxData.bic', 8, 11);

        if (Iban::requiresBic($iban)) {
            Validator::requireNotNull($bic, 'taxData.bic', sprintf('for an iban starting with %s', mb_substr($iban, 0, 2)));
        }
    }

    public function type(): VehicleRegistrationDurablePowerOfAttorneyCompanyTaxDataType
    {
        return VehicleRegistrationDurablePowerOfAttorneyCompanyTaxDataType::SepaDirectDebitMandate;
    }

    /**
     * Convert the tax data to an associative array, omitting the BIC when null.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $data = [
            'type' => $this->type()->value,
            'iban' => $this->iban,
        ];

        if ($this->bic !== null) {
            $data['bic'] = $this->bic;
        }

        return $data;
    }
}
