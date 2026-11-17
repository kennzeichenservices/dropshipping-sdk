<?php

declare(strict_types=1);

namespace Dropshipping\DTO\Requests;

use Dropshipping\Enums\VehicleRegistrationDurablePowerOfAttorneyCompanyTaxDataType;

/**
 * How the company of a durable power of attorney settles the motor vehicle tax.
 *
 * The API discriminates the variant by the `type` property, so every
 * implementation serializes its own {@see type()}:
 *
 * - {@see VehicleRegistrationDurablePowerOfAttorneySepaDirectDebitMandateTaxData}:
 *   a SEPA direct debit mandate with IBAN and, where needed, BIC.
 * - {@see VehicleRegistrationDurablePowerOfAttorneyCustomsMajorCustomerTaxData}:
 *   a major customer ID of the customs administration, replacing the mandate.
 */
interface VehicleRegistrationDurablePowerOfAttorneyCompanyTaxDataInterface
{
    /**
     * The discriminator value identifying this variant.
     */
    public function type(): VehicleRegistrationDurablePowerOfAttorneyCompanyTaxDataType;

    /**
     * Convert the tax data to an associative array, discriminator included.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array;
}
