<?php

declare(strict_types=1);

namespace Dropshipping\Enums;

/**
 * How the company behind a durable power of attorney settles the motor vehicle
 * tax: through the customs administration with a major customer ID, or by a
 * SEPA direct debit mandate.
 *
 * Discriminator of `company.taxData`. The spec names the schema
 * `VehicleRegistrationRequestVehicleHolderTypeVehicleRegistrationDurablePowerOfAttorneyCompanyTaxDataType`,
 * shortened here.
 */
enum VehicleRegistrationDurablePowerOfAttorneyCompanyTaxDataType: string
{
    case CustomsMajorCustomer = 'CUSTOMS_MAJOR_CUSTOMER';
    case SepaDirectDebitMandate = 'SEPA_DIRECT_DEBIT_MANDATE';

    /**
     * Placeholder for a tax data type this SDK version does not know.
     *
     * Never sent by the API and never sent to it. Assigned locally when a
     * response carries an unmodelled type.
     */
    case Unknown = 'UNKNOWN';
}
