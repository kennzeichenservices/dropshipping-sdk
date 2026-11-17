<?php

declare(strict_types=1);

namespace Dropshipping\DTO\Requests;

use Dropshipping\Enums\VehicleRegistrationDurablePowerOfAttorneyCompanyTaxDataType;
use Dropshipping\Exceptions\DropshippingException;
use Dropshipping\Support\Validator;

/**
 * Tax data of a durable power of attorney settling the motor vehicle tax through
 * the customs administration instead of a SEPA direct debit mandate.
 *
 * The spec marks `customsMajorCustomerId` as required and nullable at once. A
 * customs variant without the ID says nothing, so the SDK requires it.
 */
final readonly class VehicleRegistrationDurablePowerOfAttorneyCustomsMajorCustomerTaxData implements VehicleRegistrationDurablePowerOfAttorneyCompanyTaxDataInterface
{
    /** Three digits, one upper case letter, four digits, e.g. "123A4567". */
    private const CUSTOMS_MAJOR_CUSTOMER_ID_PATTERN = '/^[0-9]{3}[A-Z][0-9]{4}$/D';

    /**
     * @param string $customsMajorCustomerId Major customer ID of the customs administration (Zoll-GK-Nr.), three digits, an upper case letter and four digits.
     *
     * @throws DropshippingException When the ID does not have the documented shape.
     */
    public function __construct(
        public string $customsMajorCustomerId,
    ) {
        Validator::requirePattern(
            $customsMajorCustomerId,
            'taxData.customsMajorCustomerId',
            self::CUSTOMS_MAJOR_CUSTOMER_ID_PATTERN,
            'be three digits, an upper case letter and four digits',
        );
    }

    public function type(): VehicleRegistrationDurablePowerOfAttorneyCompanyTaxDataType
    {
        return VehicleRegistrationDurablePowerOfAttorneyCompanyTaxDataType::CustomsMajorCustomer;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'type' => $this->type()->value,
            'customsMajorCustomerId' => $this->customsMajorCustomerId,
        ];
    }
}
